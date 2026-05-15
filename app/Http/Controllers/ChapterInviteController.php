<?php

namespace App\Http\Controllers;

use App\Mail\InvitationAcceptedMail;
use App\Models\ChapterInvitation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class ChapterInviteController extends Controller
{
    /**
     * Mostra la pagina dell'invito.
     *
     * - Utente non loggato → salva token in sessione e manda al register
     * - Utente loggato → mostra pagina di conferma
     */
    public function show(string $token): View|RedirectResponse
    {
        $invitation = ChapterInvitation::with(['chapter', 'invitedBy'])
            ->where('token', $token)
            ->firstOrFail();

        // Invito non più valido
        if (! $invitation->isValid()) {
            $reason = match ($invitation->status) {
                'accepted' => 'Questo invito è già stato utilizzato.',
                'revoked'  => 'Questo invito è stato revocato.',
                'expired'  => 'Questo invito è scaduto.',
                default    => 'Questo invito non è più valido.',
            };

            return redirect()->route('login')->with('error', $reason);
        }

        // Salva token in sessione (serve sia per utente non loggato che per loggato)
        session(['chapter_invitation_token' => $token]);

        // Se non loggato → redirect al register
        if (! Auth::check()) {
            return redirect()->route('register')->with([
                'invitation_planet' => $invitation->chapter?->name,
                'invitation_by'     => $invitation->invitedBy?->name,
            ]);
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Se già membro di questo pianeta
        $alreadyMember = \DB::table('chapter_members')
            ->where('chapter_id', $invitation->chapter_id)
            ->where('user_id', $user->id)
            ->exists();

        return view('invito.show', compact('invitation', 'alreadyMember'));
    }

    /**
     * Accetta l'invito per un utente già loggato.
     */
    public function accept(Request $request, string $token): RedirectResponse
    {
        $invitation = ChapterInvitation::with(['chapter', 'invitedBy'])
            ->where('token', $token)
            ->firstOrFail();

        if (! $invitation->isValid()) {
            return redirect()->route('dashboard')->with('error', 'Invito non più valido.');
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();

        $invitation->accept($user);

        // Notifica email al leader/invitante
        if ($invitation->invitedBy && $invitation->invitedBy->email) {
            try {
                Mail::to($invitation->invitedBy->email)
                    ->send(new InvitationAcceptedMail($invitation, $user));
            } catch (\Throwable) {
                // Non bloccare il flusso se l'email fallisce
            }
        }

        session()->forget('chapter_invitation_token');

        return redirect()->route('dashboard')->with(
            'success',
            'Benvenuto nel Pianeta ' . ($invitation->chapter?->name ?? '') . '!'
        );
    }
}
