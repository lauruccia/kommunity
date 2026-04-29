<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ConversationController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'filter' => ['nullable', 'in:all,unread,favorites'],
        ]);

        $conversations = $this->conversationList($user, $filters);

        return view('conversations.index', [
            'conversations' => $conversations,
            'members' => $this->availableMembers($user),
            'filters' => $filters,
            'unreadCount' => $conversations->where('has_unread', true)->count(),
        ]);
    }

    public function show(Request $request, Conversation $conversation): View
    {
        abort_unless($conversation->participants()->where('users.id', $request->user()->id)->exists(), 403);

        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'filter' => ['nullable', 'in:all,unread,favorites'],
        ]);

        $conversation->load(['participants.memberProfile', 'messages.user.memberProfile']);
        $conversation->participants()->updateExistingPivot($request->user()->id, [
            'last_read_at' => now(),
        ]);

        $conversations = $this->conversationList($request->user(), $filters);

        return view('conversations.show', [
            'conversation' => $conversation,
            'conversations' => $conversations,
            'members' => $this->availableMembers($request->user()),
            'filters' => $filters,
            'unreadCount' => $conversations->where('has_unread', true)->count(),
        ]);
    }

    private function conversationList(User $user, array $filters = [])
    {
        $conversations = Conversation::query()
            ->with(['participants.memberProfile', 'messages.user'])
            ->whereHas('participants', fn ($query) => $query->where('users.id', $user->id))
            ->latest('updated_at')
            ->get();

        $conversations = $conversations
            ->map(function (Conversation $conversation) use ($user) {
                $otherParticipant = $conversation->participants->firstWhere('id', '!=', $user->id);
                $lastMessage = $conversation->messages->sortByDesc('created_at')->first();
                $myPivot = $conversation->participants->firstWhere('id', $user->id)?->pivot;
                $hasUnread = $lastMessage && (! $myPivot?->last_read_at || $lastMessage->created_at->gt($myPivot->last_read_at));

                $conversation->setAttribute('other_participant', $otherParticipant);
                $conversation->setAttribute('last_message', $lastMessage);
                $conversation->setAttribute('has_unread', $hasUnread);

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
        }

        return redirect()->route('conversations.show', $conversation);
    }

    public function storeMessage(Request $request, Conversation $conversation): RedirectResponse
    {
        abort_unless($conversation->participants()->where('users.id', $request->user()->id)->exists(), 403);

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

        return back()->with('status', 'message-sent');
    }
}
