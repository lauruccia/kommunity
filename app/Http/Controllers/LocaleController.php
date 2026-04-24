<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LocaleController extends Controller
{
    protected array $supported = ['it', 'en'];

    public function switch(Request $request, string $locale): RedirectResponse
    {
        if (! in_array($locale, $this->supported)) {
            abort(404);
        }

        // Salva in sessione (immediato per tutti)
        session(['locale' => $locale]);

        // Salva permanentemente per utenti autenticati
        if (auth()->check()) {
            auth()->user()->update(['locale' => $locale]);
        }

        return redirect()->back()->withHeaders([
            'Vary' => 'Accept-Language',
        ]);
    }
}
