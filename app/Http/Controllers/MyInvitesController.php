<?php

namespace App\Http\Controllers;

use App\Enums\SubscriptionStatus;
use App\Mail\ChapterInvitationMail;
use App\Models\Chapter;
use App\Models\ChapterInvitation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class MyInvitesController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();

        // Utenti registrati tramite link referral
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

        // Inviti via email inviati direttamente dall'utente
        $sentInvitations = ChapterInvitation::where('invited_by_user_id', $user->id)
            ->with('chapter')
            ->orderByDesc('created_at')
            ->get();

        // Pianeti disponibili per il form di invito:
        // admin/super-admin vedono tutti i pianeti, gli utenti normali solo i propri.
        $isAdmin     = $user->hasAnyRole(['super-admin', 'admin-community']);
        $userPlanets = $isAdmin
            ? Chapter::orderBy('name')->get(['id', 'name'])
            : $user->planets()->orderBy('name')->get(['chapters.id', 'chapters.name']);

        // Statistiche
        $stats = [
            'email_sent'      => $sentInvitations->count(),
            'email_accepted'  => $sentInvitations->where('status', 'accepted')->count(),
            'link_total'      => $invitedUsers->count(),
            'link_subscribed' => $invitedUsers->filter(fn ($u) => $u->subscriptions->isNotEmpty())->count(),
        ];

        // Pianeta di default per il form: primary_chapter_id, poi active_chapter_id, poi primo della lista
        $defaultPlanetId = $user->memberProfile?->primary_chapter_id
            ?? $user->memberProfile?->active_chapter_id
            ?? $userPlanets->first()?->id;

        return view('members.invites', compact('invitedUsers', 'sentInvitations', 'userPlanets', 'stats', 'defaultPlanetId'));
    }

    public function invite(Request $request): RedirectResponse
    {
        $user = auth()->user();

        $request->validate([
            'email'      => ['required', 'email', 'max:255'],
            'chapter_id' => ['required', 'exists:chapters,id'],
            'message'    => ['nullable', 'string', 'max:500'],
        ]);

        // Verifica appartenenza al Pianeta selezionato.
        // Admin/super-admin possono invitare su qualsiasi pianeta senza essere membri.
        $isAdmin = $user->hasAnyRole(['super-admin', 'admin-community']);
        if ($isAdmin) {
            $chapter = Chapter::find($request->chapter_id);
        } else {
            $chapter = $user->planets()->where('chapters.id', $request->chapter_id)->first();
        }
        if (! $chapter) {
            return back()->withErrors(['chapter_id' => 'Non appartieni a questo Pianeta.'])->withInput();
        }

        // Controlla se esiste già un invito pending per questa email+pianeta
        $existing = ChapterInvitation::where('email', $request->email)
            ->where('chapter_id', $request->chapter_id)
            ->where('status', 'pending')
            ->exists();

        if ($existing) {
            return back()->withErrors(['email' => 'Hai già un invito in sospeso per questa email in questo Pianeta.'])->withInput();
        }

        $invitation = ChapterInvitation::create([
            'chapter_id'         => $request->chapter_id,
            'invited_by_user_id' => $user->id,
            'email'              => $request->email,
            'message'            => $request->message,
            'expires_at'         => now()->addDays(30),
        ]);

        $invitation->load('chapter', 'invitedBy');

        Mail::to($request->email)->send(new ChapterInvitationMail($invitation));

        return back()->with('invite_success', 'Invito inviato a ' . $request->email . '!');
    }

    public function revoke(ChapterInvitation $invitation): RedirectResponse
    {
        abort_if($invitation->invited_by_user_id !== auth()->id(), 403);
        abort_if($invitation->status !== 'pending', 422);

        $invitation->update(['status' => 'revoked']);

        return back()->with('invite_success', 'Invito annullato.');
    }
}
