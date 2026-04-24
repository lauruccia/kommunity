<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    protected array $supportedLocales = ['it', 'en'];

    public function handle(Request $request, Closure $next): Response
    {
        $locale = null;

        // 1. Utente autenticato: usa la sua preferenza salvata
        if (auth()->check() && in_array(auth()->user()->locale, $this->supportedLocales)) {
            $locale = auth()->user()->locale;
        }

        // 2. Sessione (usata dopo il cambio lingua immediato)
        if (! $locale && in_array(session('locale'), $this->supportedLocales)) {
            $locale = session('locale');
        }

        // 3. Default app
        if (! $locale) {
            $locale = config('app.locale', 'it');
        }

        App::setLocale($locale);

        return $next($request);
    }
}
