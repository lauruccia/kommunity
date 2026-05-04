<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class MediaController extends Controller
{
    public function show(string $path): Response
    {
        // ── Posizione 1: disco "public" configurato ───────────────────────────
        // In locale punta a storage/app/public/.
        // Su cPanel, se MEDIA_DISK_ROOT=/home2/kommunity/public_html/media è nel
        // .env, punta direttamente alla web root → file trovato qui.
        if (Storage::disk('public')->exists($path)) {
            return response()->file(Storage::disk('public')->path($path));
        }

        // ── Posizione 2: fallback cPanel ─────────────────────────────────────
        // Quando MEDIA_DISK_ROOT non è impostato nel .env, Laravel salva i file
        // in storage/app/public/, MA se il disco è stato configurato diversamente
        // in passato o i file sono stati caricati via File Manager cPanel, si
        // trovano in public_html/media/.
        // base_path() = /home2/kommunity/kommunity/ → dirname = /home2/kommunity/
        $cPanelPath = dirname(base_path()) . '/public_html/media/' . ltrim($path, '/');
        if (is_file($cPanelPath) && is_readable($cPanelPath)) {
            return response()->file($cPanelPath);
        }

        abort(404);
    }
}
