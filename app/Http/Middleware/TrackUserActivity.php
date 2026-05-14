<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Aggiorna silenziosamente last_seen_at (e la pagina corrente) ad ogni
 * richiesta autenticata.
 *
 * Strategia:
 *  - last_seen_at  → aggiornato max 1 volta al minuto (riduce i write su DB)
 *  - last_seen_url / last_seen_route → aggiornati solo su vere GET di pagina
 *    (esclude Livewire AJAX, JSON API, form POST/PATCH/DELETE)
 */
class TrackUserActivity
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $user = $request->user();

        if (! $user) {
            return $response;
        }

        $now        = now();
        $isLivewire = $request->hasHeader('X-Livewire');
        $isPageLoad = $request->isMethod('GET')
            && ! $isLivewire
            && ! $request->expectsJson();

        // Throttle: aggiorna last_seen_at al massimo ogni 60 secondi
        $seenStale = ! $user->last_seen_at
            || $user->last_seen_at->lt($now->clone()->subMinute());

        // Se la sessione è fresca E non è un page load reale → salta
        if (! $seenStale && ! $isPageLoad) {
            return $response;
        }

        $updates = ['last_seen_at' => $now];

        if ($isPageLoad) {
            $routeName = $request->route()?->getName() ?? '';

            $updates['last_seen_url']   = '/' . ltrim($request->path(), '/');
            $updates['last_seen_route'] = $routeName;
        }

        $user->forceFill($updates)->saveQuietly();

        return $response;
    }
}
