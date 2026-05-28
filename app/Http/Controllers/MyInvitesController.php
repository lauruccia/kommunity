<?php

namespace App\Http\Controllers;

use App\Enums\SubscriptionStatus;
use Illuminate\View\View;

class MyInvitesController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();

        // Carica tutti gli utenti invitati con relazioni utili
        $invitedUsers = $user->invitedUsers()
            ->with([
                'memberProfile',
                'subscriptions' => fn ($q) => $q
                    ->whereIn('status', [
                        SubscriptionStatus::Active->value,
                        SubscriptionStatus::Trial->value,
                    ])
                    ->latest('starts_at')
                    ->limit(1),
            ])
            ->orderByDesc('created_at')
            ->get();

        // Statistiche rapide
        $stats = [
            'total'        => $invitedUsers->count(),
            'verified'     => $invitedUsers->filter(fn ($u) => $u->email_verified_at)->count(),
            'profile_done' => $invitedUsers->filter(fn ($u) => $u->memberProfile?->onboarding_completed)->count(),
            'subscribed'   => $invitedUsers->filter(fn ($u) => $u->subscriptions->isNotEmpty())->count(),
        ];

        return view('members.invites', compact('invitedUsers', 'stats'));
    }
}
