<?php

namespace App\Http\Controllers;

use App\Models\Chapter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PlanetContextController extends Controller
{
    /**
     * Cambia il Pianeta attivo dell'utente.
     *
     * Il Pianeta attivo (active_chapter_id) determina il contesto di navigazione:
     * directory, forum, messaggi e 1-to-1 vengono filtrati per questo Pianeta.
     *
     * Requisito: l'utente deve essere membro attivo del Pianeta scelto
     * (riga in chapter_members con status = 'active').
     */
    public function switch(Request $request, Chapter $chapter): RedirectResponse
    {
        $user = $request->user();

        // Verifica che l'utente appartenga a questo Pianeta
        $isMember = $user->planets()->where('chapters.id', $chapter->id)->exists();

        if (! $isMember) {
            abort(403, 'Non sei utente di questo Pianeta.');
        }

        // Aggiorna il pianeta attivo senza triggerare il listener profession-limit
        $user->memberProfile()->update(['active_chapter_id' => $chapter->id]);

        // Invalida la cache della directory (ordine random per pianeta)
        \Illuminate\Support\Facades\Cache::forget('directory.random_ids.v1');

        // Resta sulla pagina da cui è avvenuto il cambio Pianeta.
        // (Prima reindirizzava sempre alla chat: comportamento indesiderato.)
        // Se il riferimento manca o punta alla chat di un Pianeta, si torna alla dashboard.
        $back = url()->previous();

        if (! $back || $back === url()->current() || str_contains($back, '/chat')) {
            $back = route('dashboard');
        }

        return redirect()->to($back)
            ->with('planet_switched', $chapter->name);
    }
}
