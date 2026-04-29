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
                return redirect()->route('profile.edit')
                    ->with('warning', 'Completa il tuo profilo per accedere a questa sezione.');
            }
        }

        return $next($request);
    }
}
