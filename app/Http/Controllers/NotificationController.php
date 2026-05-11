<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /** Pagina con tutte le notifiche (max 50, paginate). */
    public function index(Request $request): View
    {
        $user = $request->user();

        // Segna tutte le non lette come lette quando si apre la pagina notifiche,
        // così il badge nella navbar sparisce dopo aver visitato questa pagina.
        $user->unreadNotifications()->update(['read_at' => now()]);

        $notifications = $user
            ->notifications()
            ->latest()
            ->paginate(20);

        return view('notifications.index', compact('notifications'));
    }

    public function markAsRead(Request $request, string $id): JsonResponse
    {
        $notification = $request->user()
            ->notifications()
            ->findOrFail($id);

        $notification->markAsRead();

        return response()->json(['ok' => true]);
    }

    public function markAllAsRead(Request $request): JsonResponse