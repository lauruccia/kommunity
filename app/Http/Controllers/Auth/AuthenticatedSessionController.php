<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = $request->user();
        $intended = (string) $request->session()->get('url.intended', '');

        if ($user && str_starts_with(parse_url($intended, PHP_URL_PATH) ?: '', '/admin') && ! $this->canAccessAdmin($user)) {
            $request->session()->forget('url.intended');

            return redirect()->route('dashboard')
                ->with('warning', 'Hai effettuato l\'accesso come membro. Per entrare in amministrazione usa un account admin.');
        }

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }

    private function canAccessAdmin($user): bool
    {
        return $user->hasAnyRole(['super-admin', 'admin-community', 'leader-capitolo'])
            || $user->hasAnyPermission([
                'gestire-eventi',
                'gestire-utenti',
                'assegnare-ruoli',
                'assegnare-permessi',
                'gestire-capitoli',
            ]);
    }
}
