<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

/**
 * Impersona un utente: l'admin accede come se fosse quel membro.
 * L'ID admin originale viene salvato in sessione per consentire il ritorno.
 */
class ImpersonateController extends Controller
{
    /** Inizia l'impersonificazione */
    public function start(Request $request, User $user): RedirectResponse
    {
        $admin = auth()->user();

        // Solo super-admin può impersonare
        if (! $admin?->hasRole('super-admin')) {
            abort(403, 'Solo il super-admin può impersonare un utente.');
        }

        // Non impersonare se stessi
        if ($admin->is($user)) {
            return back()->with('error', 'Non puoi impersonare te stesso.');
        }

        // Non impersonare altri super-admin
        if ($user->hasRole('super-admin')) {
            return back()->with('error', 'Non puoi impersonare un altro super-admin.');
        }

        // Salva l'ID admin originale in sessione
        Session::put('impersonating_admin_id', $admin->id);

        Auth::login($user);

        return redirect()->route('dashboard')
            ->with('success', 'Stai navigando come: ' . $user->name . '. Torna all\'admin per uscire.');
    }

    /** Termina l'impersonificazione e ripristina l'admin */
    public function stop(Request $request): RedirectResponse
    {
        $adminId = Session::pull('impersonating_admin_id');

        if (! $adminId) {
            return redirect()->route('dashboard');
        }

        $admin = User::find($adminId);

        if (! $admin) {
            Auth::logout();
            return redirect()->route('login');
        }

        Auth::login($admin);

        return redirect('/admin/users')
            ->with('success', 'Impersonificazione terminata. Sei tornato come: ' . $admin->name);
    }
}
