<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\User;
use App\Notifications\NewMessageNotification;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ConversationController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request): View
    {
        $user = $request->user();
        $this->markUserSeen($user);
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'filter' => ['nullable', 'in:all,unread,favorites'],
            'to'     => ['nullable', 'integer', 'exists:users,id'],
        ]);

        $conversations = $this->conversationList($user, $filters);

        // Se è stato passato ?to=ID dal profilo membro, apri il modale "Nuovo messaggio"
        // con il destinatario precompilato. NIENTE invio automatico.
        $prefillRecipientId = null;
        if (! empty($filters['to']) && (int) $filters['to'] !== $user->id) {
            $prefillRecipientId = (int) $filters['to'];
        }

        return view('conversations.index', [
            'conversations' => $conversations,
            'members' => $this->availableMembers($user),
            'filters' => $filters,
            'unreadCount' => $conversations->where('has_unread', true)->count(),
            'prefillRecipientId' => $prefillRecipientId,
        ]);
    }

    public function show(Request $request, Conversation $conversation): View
    {
        $this->authorize('view', $conversation);
        $this->markUserSeen($request->user());

        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'filter' => ['nullable', 'in:all,unread,favorites'],
        ]);

        $conversation->load([
            'participants.memberProfile.city',
            'participants.memberProfile.profession',
            'participants.memberProfile.category',
            'participants.memberOnepage',
        ]);

        // Carica solo gli ultimi 100 messaggi — evita crash su conversazioni lunghe
        $messages = $conversation->messages()
            ->with('user.memberProfile')
            ->latest()
            ->take(100)
            ->get()
            ->reverse()
            ->values();

        $conversation->participants()->updateExistingPivot($request->user()->id, [
            'last_read_at' => now(),
        ]);

        $conversations = $this->conversationList($request->user(), $filters);

        return view('conversations.show', [
            'conversation' => $conversation,
            'conversations' => $conversations,
            'messages'      => $messages,
            'members' => $this->availableMembers($request->user()),
            'filters' => $filters,
            'unreadCount' => $conversations->where('has_unread', true)->count(),
        ]);
    }

    private function conversationList(User $user, array $filters = [])
    {
        // Carichiamo solo l'ULTIMO messaggio per conversazione invece di tutti i messaggi.
        // Questo evita N*M record in memoria quando l'utente ha molte conversazioni.
        $conversations = Conversation::query()
            ->with([
                'participants.memberProfile',
                // lastMessage è una relazione HasOne ordinata per created_at DESC (vedi model Conversation)
                'lastMessage.user',
            ])
            ->whereHas('participants', fn ($query) => $query->where('users.id', $user->id))
            ->latest('updated_at')
            ->limit(200) // hard cap: nessun utente ha bisogno di più di 200 conversazioni in lista
            ->get();

        $conversations = $conversations
            ->map(function (Conversation $conversation) use ($user) {
                $otherParticipant = $conversation->participants->firstWhere('id', '!=', $user->id);
                $lastMessage = $conversation->lastMessage;
                $myPivot = $conversation->participants->firstWhere('id', $user->id)?->pivot;
                $hasUnread = $lastMessage && (! $myPivot?->last_read_at || $lastMessage->created_at->gt($myPivot->last_read_at));
                $lastReadAt = $myPivot?->last_read_at ? Carbon::parse($myPivot->last_read_at) : null;
                // Conta messaggi non letti via query puntuale (eseguita solo se serve il conteggio)
                $unreadCount = $hasUnread
                    ? $conversation->messages()
                        ->where('user_id', '!=', $user->id)
                        ->when($lastReadAt, fn ($q) => $q->where('created_at', '>', $lastReadAt))
                        ->count()
                    : 0;

                $conversation->setAttribute('other_participant', $otherParticipant);
                $conversation->setAttribute('last_message', $lastMessage);
                $conversation->setAttribute('has_unread', $hasUnread);
                $conversation->setAttribute('unread_count', $unreadCount);

                return $conversation;
            })
            ->filter(function (Conversation $conversation) use ($filters) {
                if (($filters['filter'] ?? 'all') === 'unread' && ! $conversation->getAttribute('has_unread')) {
                    return false;
                }

                if (blank($filters['search'] ?? null)) {
                    return true;
                }

                $search = mb_strtolower((string) $filters['search']);
                $name = mb_strtolower((string) optional($conversation->getAttribute('other_participant'))->name);
                $subject = mb_strtolower((string) $conversation->subject);
                $lastMessage = mb_strtolower((string) optional($conversation->getAttribute('last_message'))->body);

                return str_contains($name, $search)
                    || str_contains($subject, $search)
                    || str_contains($lastMessage, $search);
            })
            ->values();

        return $conversations;
    }

    private function availableMembers(User $user)
    {
        return User::query()
            ->with('memberProfile')
            ->whereKeyNot($user->id)
            ->whereHas('memberProfile', fn ($query) => $query->where('is_active', true))
            ->orderBy('name')
            ->get();
    }

    public function start(Request $request): RedirectResponse
    {
        $this->markUserSeen($request->user());

        $data = $request->validate([
            'recipient_id' => ['required', 'exists:users,id'],
            'message' => ['nullable', 'string', 'max:3000'],
        ]);

        $userId = $request->user()->id;
        $recipientId = (int) $data['recipient_id'];
        $message = trim((string) ($data['message'] ?? ''));

        abort_if($recipientId === $userId, 422, 'Non puoi avviare una conversazione con te stesso.');

        $conversation = Conversation::query()
            ->whereHas('participants', fn ($query) => $query->where('users.id', $userId))
            ->whereHas('participants', fn ($query) => $query->where('users.id', $recipientId))
            ->withCount('participants')
            ->get()
            ->firstWhere('participants_count', 2);

        if (! $conversation) {
            $conversation = Conversation::query()->create([
                'subject' => 'Conversazione privata',
            ]);

            $conversation->participants()->sync([
                $userId => ['last_read_at' => now()],
                $recipientId => ['last_read_at' => null],
            ]);
        }

        if ($message !== '') {
            $conversation->messages()->create([
                'user_id' => $userId,
                'body' => $message,
            ]);

            $conversation->touch();

            // Notifica il destinatario
            $sender = $request->user();
            $recipient = User::find($recipientId);
            $recipient?->notify(new NewMessageNotification($conversation, $sender, $message));
        }

        return redirect()->route('conversations.show', $conversation);
    }

    public function storeMessage(Request $request, Conversation $conversation): RedirectResponse
    {
        $this->authorize('sendMessage', $conversation);
        $this->markUserSeen($request->user());

        $data = $request->validate([
            'body' => ['required', 'string', 'max:3000'],
        ]);

        $conversation->messages()->create([
            'user_id' => $request->user()->id,
            'body' => $data['body'],
        ]);

        $conversation->touch();

        $conversation->participants()->updateExistingPivot($request->user()->id, [
            'last_read_at' => now(),
        ]);

        // Notifica gli altri partecipanti
        $sender = $request->user();
        $conversation->participants()
            ->where('users.id', '!=', $sender->id)
            ->get()
            ->each(fn (User $participant) => $participant->notify(
                new NewMessageNotification($conversation, $sender, $data['body'])
            ));

        return back()->with('status', 'message-sent');
    }

    private function markUserSeen(User $user): void
    {
        $user->forceFill(['last_seen_at' => now()])->saveQuietly();
    }
}
