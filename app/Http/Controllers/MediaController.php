<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class MediaController extends Controller
{
    public function show(string $path): Response
    {
        $clean = ltrim($path, '/');

        // ── Blocco path traversal ─────────────────────────────────────────────
        // Rifiuta qualsiasi path con "..", null-byte, o caratteri di controllo.
        // Consenti solo: lettere, cifre, "/", ".", "-", "_".
        if (
            str_contains($clean, '..')
            || str_contains($clean, "\0")
            || ! preg_match('#^[a-zA-Z0-9/.\-_]+$#', $clean)
        ) {
            abort(400);
        }

        // ── 1. Disco configurato (MEDIA_DISK_ROOT o storage/app/public) ───────
        if (Storage::disk('public')->exists($clean)) {
            return response()->file(Storage::disk('public')->path($clean));
        }

        // ── 2. storage/app/public/ (percorso diretto, senza disco) ───────────
        $storagePath = storage_path('app/public/' . $clean);
        if (is_file($storagePath) && is_readable($storagePath)) {
            return response()->file($storagePath);
        }

        // ── 3. public_html/media/ tramite dirname(base_path()) ───────────────
        // base_path() = /home2/USERNAME/APPFOLDER → dirname = /home2/USERNAME/
        $cPanelPath = dirname(base_path()) . '/public_html/media/' . $clean;
        if (is_file($cPanelPath) && is_readable($cPanelPath)) {
            return response()->file($cPanelPath);
        }

        // ── 4. public_html/media/ tramite HOME di sistema ────────────────────
        // Alcune configurazioni cPanel hanno HOME impostato correttamente.
        $home = rtrim(env('HOME', ''), '/');
        if ($home) {
            $homePath = $home . '/public_html/media/' . $clean;
            if (is_file($homePath) && is_readable($homePath)) {
                return response()->file($homePath);
            }
        }

        // ── 5. Percorso relativo a public_path() ─────────────────────────────
        // Utile se esiste un symlink storage/ dentro public_html/
        $publicStoragePath = public_path('storage/' . $clean);
        if (is_file($publicStoragePath) && is_readable($publicStoragePath)) {
            return response()->file($publicStoragePath);
        }

        \Illuminate\Support\Facades\Log::warning('MediaController: file non trovato', [
            'path'         => $clean,
            'disk_root'    => Storage::disk('public')->path(''),
            'storage_path' => $storagePath,
            'cpanel_path'  => $cPanelPath,
        ]);

        abort(404);
    }
}
