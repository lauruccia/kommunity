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
        // Se il link viene aperto da WhatsApp/Gmail/Safari senza cookie, logghiamo
        // il nuovo membro. Se invece esiste già una sessione diversa (es. admin
        // aperto nello stesso browser), NON la sostituiamo: verificare una mail non
        // deve mai rubare la sessione corrente e causare 403 su /admin.
        $currentUser = $request->user();

        if (! $currentUser) {
            Auth::login($user, remember: true);
            $request->session()->regenerate();
        } elseif ($currentUser->id !== $user->id) {
            $this->verifyUser($user);

            $target = $currentUser->hasAnyRole(['super-admin', 'admin-community', 'leader-capitolo'])
                || $currentUser->hasAnyPermission([
                    'gestire-eventi',
                    'gestire-utenti',
                    'assegnare-ruoli',
                    'assegnare-permessi',
                    'gestire-capitoli',
                ])
                    ? url('/admin')
                    : route('dashboard', absolute: false);

            return redirect()->to($target)
                ->with('success', 'Account verificato. La sessione corrente non è stata modificata.');
        }

        // Email già verificata → porta al profilo, non al wizard dashboard.
        if ($user->hasVerifiedEmail()) {
            return redirect()->route('profile.edit')
                ->with('success', 'Email verificata. Completa il profilo per attivare tutte le funzioni.');
        }

        $this->verifyUser($user);

        return redirect()->route('profile.edit')
            ->with('success', 'Account attivato. Completa il profilo per entrare nella community.');
    }

    private function verifyUser(User $user): void
    {
        if (! $user->hasVerifiedEmail() && $user->markEmailAsVerified()) {
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
    }
}
