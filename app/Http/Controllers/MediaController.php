<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class MediaController extends Controller
{
    public function show(string $path): Response
    {
        // Cerca prima nel disco public (su cPanel → public_html/media/)
        if (Storage::disk('public')->exists($path)) {
            return response()->file(Storage::disk('public')->path($path));
        }

        // Fallback 1: vecchio path con prefisso members/ in storage/app/public/
        // (file caricati prima della migrazione a public_html/media/)
        $legacyPath = storage_path('app/public/members/' . ltrim($path, '/'));
        if (file_exists($legacyPath) && is_file($legacyPath)) {
            return response()->file($legacyPath);
        }

        // Fallback 2: storage/app/public/ senza prefisso members/
        $legacyPath2 = storage_path('app/public/' . ltrim($path, '/'));
        if (file_exists($legacyPath2) && is_file($legacyPath2)) {
            return response()->file($legacyPath2);
        }

        abort(404);
    }
}
