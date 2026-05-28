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

        // Chi ha emesso l'invito deve essere ancora membro attivo del pianeta.
        // Questo impedisce di usare un invito inviato da qualcuno che nel frattempo
        // è uscito dal pianeta (o che non ne ha mai fatto parte).
        if (! $this->inviterIsMember($invitation)) {
            return redirect()->route('login')
                ->with('error', 'Chi ti ha invitato non è più membro di questo Pianeta. L\'invito non è valido.');
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

        // Ricontrollo appartenenza anche al momento dell'accettazione
        if (! $this->inviterIsMember($invitation)) {
            return redirect()->route('dashboard')
                ->with('error', 'Chi ti ha invitato non è più membro di questo Pianeta. L\'invito non è valido.');
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

    /**
     * Verifica che chi ha emesso l'invito sia ancora un membro attivo del pianeta.
     *
     * Questo controllo impedisce due scenari di abuso:
     *   1. Un utente che ha lasciato il pianeta ha già condiviso un link di invito.
     *   2. Qualcuno modifica il token per usarlo su un pianeta dove l'invitante non è mai stato.
     *      (caso teorico: il token è opaco e casuale, ma il controllo aggiunge un livello extra)
     *
     * Eccezione: admin e super-admin possono invitare su qualsiasi pianeta
     * senza essere membri attivi — il controllo è sempre superato per questi ruoli.
     */
    private function inviterIsMember(ChapterInvitation $invitation): bool
    {
        // Se l'invito è stato emesso dal sistema (nessun invitante), lo consideriamo valido.
        if (! $invitation->invited_by_user_id || ! $invitation->chapter_id) {
            return true;
        }

        // Admin/super-admin possono invitare su qualsiasi pianeta.
        $inviter = \App\Models\User::find($invitation->invited_by_user_id);
        if ($inviter && $inviter->hasAnyRole(['super-admin', 'admin-community'])) {
            return true;
        }

        return \DB::table('chapter_members')
            ->where('chapter_id', $invitation->chapter_id)
            ->where('user_id', $invitation->invited_by_user_id)
            ->where('status', 'active')
            ->exists();
    }
}
