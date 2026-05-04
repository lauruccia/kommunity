<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOnboardingComplete
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user) {
            $profile = $user->memberProfile()->first();

            if (! $profile || ! $profile->onboarding_completed) {
                // Le chiamate AJAX/fetch (Alpine.js, fetch API) ricevono JSON,
                // non un redirect HTML che causerebbe errori silenziosi lato JS.
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json([
                        'message' => 'Completa il tuo profilo per accedere a questa sezione.',
                        'redirect' => route('profile.edit'),
                    ], 403);
                }

                return redirect()->route('profile.edit')
                    ->with('warning', 'Completa il tuo profilo per accedere a questa sezione.');
            }
        }

        return $next($request);
    }
}
