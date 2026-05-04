<?php

namespace App\Http\Controllers\Auth;

use App\Enums\MemberProfileStatus;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\NewMemberVerifiedNotification;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;

class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     * Auto-approva il profilo membro e notifica gli admin.
     */
    public function __invoke(EmailVerificationRequest $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(route('dashboard', absolute: false).'?verified=1');
        }

        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));

            // ── Auto-approvazione ────────────────────────────────────────────
            // Dopo la verifica email il membro diventa automaticamente attivo
            // e visibile in directory senza attendere l'approvazione manuale.
            try {
                $request->user()->memberProfile()->update([
                    'is_active'               => true,
                    'is_visible_in_directory' => true,
                    'status'                  => MemberProfileStatus::Active,
                ]);
            } catch (\Throwable $e) {
                Log::warning('Auto-approvazione profilo fallita dopo verifica email', [
                    'user_id' => $request->user()->id,
                    'error'   => $e->getMessage(),
                ]);
            }

            // ── Notifica admin ───────────────────────────────────────────────
            // Avvisa tutti gli admin/super-admin che un nuovo membro è attivo.
            try {
                $admins = User::query()
                    ->whereNotNull('email')
                    ->whereKeyNot($request->user()->id)
                    ->whereHas('roles', fn ($q) => $q->whereIn('name', ['super-admin', 'admin-community']))
                    ->get();

                foreach ($admins as $admin) {
                    $admin->notify(new NewMemberVerifiedNotification($request->user()));
                }
            } catch (\Throwable $e) {
                Log::warning('Notifica admin verifica email fallita', [
                    'user_id' => $request->user()->id,
                    'error'   => $e->getMessage(),
                ]);
            }
        }

        return redirect()->intended(route('dashboard', absolute: false).'?verified=1');
    }
}
