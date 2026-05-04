<?php

namespace App\Http\Controllers\Auth;

use App\Enums\MemberProfileStatus;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\NewMemberVerifiedNotification;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class VerifyEmailController extends Controller
{
    /**
     * Verifica l'email dell'utente e lo logga automaticamente se necessario.
     *
     * Il middleware 'signed' garantisce l'autenticità dell'URL; qui verifichiamo
     * anche che l'hash corrisponda all'email dell'utente (doppia protezione).
     *
     * Non richiede una sessione preesistente: funziona da WhatsApp, Gmail app,
     * Outlook mobile e qualsiasi browser in-app che non condivide i cookie con
     * il browser di sistema.
     */
    public function __invoke(Request $request, string $id, string $hash): RedirectResponse
    {
        /** @var User $user */
        $user = User::findOrFail($id);

        // Verifica che l'hash nel link corrisponda all'email dell'utente.
        // (La firma crittografica dell'URL è già stata validata dal middleware 'signed'.)
        if (! hash_equals(sha1($user->getEmailForVerification()), $hash)) {
            abort(403, 'Link di verifica non valido.');
        }

        // ── Auto-login se necessario ─────────────────────────────────────────
        // Scenari gestiti:
        // 1. Nessun utente loggato (WhatsApp/Gmail in-app browser) → login automatico
        // 2. Utente diverso loggato (raro, ma gestito) → logout + login come utente corretto
        // 3. Stesso utente già loggato → nessuna azione
        $currentUser = $request->user();

        if (! $currentUser) {
            Auth::login($user, remember: true);
        } elseif ($currentUser->id !== (int) $id) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            Auth::login($user, remember: true);
        }

        // Email già verificata → redirect diretto alla dashboard
        if ($user->hasVerifiedEmail()) {
            return redirect(route('dashboard', absolute: false) . '?verified=1');
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));

            // ── Auto-approvazione profilo ────────────────────────────────────
            // Dopo la verifica email il membro diventa automaticamente attivo
            // e visibile in directory senza attendere l'approvazione manuale.
            try {
                $user->memberProfile()->update([
                    'is_active'               => true,
                    'is_visible_in_directory' => true,
                    'status'                  => MemberProfileStatus::Active,
                ]);
            } catch (\Throwable $e) {
                Log::warning('Auto-approvazione profilo fallita dopo verifica email', [
                    'user_id' => $user->id,
                    'error'   => $e->getMessage(),
                ]);
            }

            // ── Notifica admin ───────────────────────────────────────────────
            // Avvisa tutti gli admin/super-admin che un nuovo membro è attivo.
            try {
                $admins = User::query()
                    ->whereNotNull('email')
                    ->whereKeyNot($user->id)
                    ->whereHas('roles', fn ($q) => $q->whereIn('name', ['super-admin', 'admin-community']))
                    ->get();

                foreach ($admins as $admin) {
                    $admin->notify(new NewMemberVerifiedNotification($user));
                }
            } catch (\Throwable $e) {
                Log::warning('Notifica admin verifica email fallita', [
                    'user_id' => $user->id,
                    'error'   => $e->getMessage(),
                ]);
            }
        }

        return redirect(route('dashboard', absolute: false) . '?verified=1');
    }
}
