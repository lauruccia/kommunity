<?php

namespace App\Http\Controllers;

use App\Models\Chapter;
use App\Models\ChapterMember;
use App\Models\PlanetChatMessage;
use App\Models\PushSubscription;
use App\Services\WebPush\WebPushService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PlanetChatController extends Controller
{
    /**
     * Mostra la chat di gruppo del pianeta.
     * Accesso consentito solo ai membri attivi del capitolo.
     */
    public function show(Chapter $chapter): View|RedirectResponse
    {
        if (! $this->isActiveMember($chapter)) {
            abort(403, __('planet_chat.not_member'));
        }

        $messages = PlanetChatMessage::query()
            ->where('chapter_id', $chapter->id)
            ->with('user:id,name,profile_photo_path')
            ->orderBy('id')
            ->latest('id')
            ->take(80)
            ->get()
            ->reverse()
            ->values();

        $memberCount = ChapterMember::where('chapter_id', $chapter->id)
            ->where('status', 'active')
            ->count();

        // Segna la chat come letta: aggiorna chat_last_read_at
        ChapterMember::where('chapter_id', $chapter->id)
            ->where('user_id', Auth::id())
            ->where('status', 'active')
            ->update(['chat_last_read_at' => now()]);

        return view('planet-chat.show', compact('chapter', 'messages', 'memberCount'));
    }

    /**
     * Invia un nuovo messaggio e notifica via push gli altri membri attivi.
     */
    public function store(Request $request, Chapter $chapter): JsonResponse
    {
        if (! $this->isActiveMember($chapter)) {
            return response()->json(['error' => __('planet_chat.not_member')], 403);
        }

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:2000'],
        ]);

        $message = PlanetChatMessage::create([
            'chapter_id' => $chapter->id,
            'user_id'    => Auth::id(),
            'body'       => $validated['body'],
        ]);

        $message->load('user:id,name,profile_photo_path');

        // Notifica push agli altri membri attivi
        $this->notifyMembers($chapter, $message);

        return response()->json([
            'message' => $this->formatMessage($message),
        ], 201);
    }

    /**
     * Polling: ritorna i messaggi con id > since_id.
     * Parametro opzionale ?limit=N per caricare gli ultimi N messaggi (popup FAB).
     */
    public function poll(Request $request, Chapter $chapter): JsonResponse
    {
        if (! $this->isActiveMember($chapter)) {
            return response()->json(['error' => __('planet_chat.not_member')], 403);
        }

        $sinceId = (int) $request->query('since', 0);
        $limit   = min((int) $request->query('limit', 50), 50);

        $query = PlanetChatMessage::query()
            ->where('chapter_id', $chapter->id)
            ->where('id', '>', $sinceId)
            ->with('user:id,name,profile_photo_path')
            ->orderBy('id', 'desc')
            ->take($limit);

        $messages = $query->get()->reverse()->values()
            ->map(fn ($m) => $this->formatMessage($m));

        return response()->json(['messages' => $messages]);
    }

    /**
     * Redirect rapido alla chat del pianeta attivo dell'utente.
     * Prima controlla active_chapter_id su MemberProfile, poi chapter_members come fallback.
     */
    public function redirect(): RedirectResponse
    {
        $user = Auth::user();

        // 1) Priorità: pianeta "primario" del profilo membro
        $chapterId = optional($user->memberProfile)->active_chapter_id;

        // 2) Fallback: primo pianeta attivo in chapter_members
        if (! $chapterId) {
            $chapterId = ChapterMember::where('user_id', $user->id)
                ->where('status', 'active')
                ->value('chapter_id');
        }

        if (! $chapterId) {
            return redirect()->route('dashboard')
                ->with('error', __('planet_chat.no_planet'));
        }

        $chapter = Chapter::find($chapterId);
        if (! $chapter) {
            return redirect()->route('dashboard')
                ->with('error', __('planet_chat.no_planet'));
        }

        return redirect()->route('planet.chat.show', $chapter);
    }

    // ────────────────────────────────────────────────────────────────────────

    private function isActiveMember(Chapter $chapter): bool
    {
        return ChapterMember::where('chapter_id', $chapter->id)
            ->where('user_id', Auth::id())
            ->where('status', 'active')
            ->exists();
    }

    private function formatMessage(PlanetChatMessage $message): array
    {
        $user = $message->user;
        return [
            'id'         => $message->id,
            'user_id'    => $message->user_id,
            'body'       => $message->body,
            'created_at' => $message->created_at->format('H:i'),
            'date'       => $message->created_at->format('d/m/Y'),
            'author'     => $user?->name ?? '—',
            'avatar'     => $user?->profile_photo_path
                                ? asset('storage/' . $user->profile_photo_path)
                                : null,
            'initials'   => $user ? mb_strtoupper(mb_substr($user->name, 0, 1)) : '?',
        ];
    }

    private function notifyMembers(Chapter $chapter, PlanetChatMessage $message): void
    {
        $pushService = app(WebPushService::class);
        if (! $pushService->isConfigured()) {
            return;
        }

        $otherMemberIds = ChapterMember::where('chapter_id', $chapter->id)
            ->where('status', 'active')
            ->where('user_id', '!=', Auth::id())
            ->pluck('user_id');

        $payload = [
            'title' => '💬 ' . $chapter->name,
            'body'  => $message->user->name . ': ' . mb_substr($message->body, 0, 100),
            'icon'  => '/images/icon-192.png',
            'url'   => route('planet.chat.show', $chapter),
            'tag'   => 'planet-chat-' . $chapter->id,
        ];

        foreach ($otherMemberIds as $userId) {
            $pushService->sendToUser($userId, $payload, 3600);
        }
    }
}
