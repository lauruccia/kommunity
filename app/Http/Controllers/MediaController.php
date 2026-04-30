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

        // Fallback: file già esistenti nel vecchio storage/app/public/ (prima della migrazione)
        $legacyPath = storage_path('app/public/' . ltrim($path, '/'));
        if (file_exists($legacyPath) && is_file($legacyPath)) {
            return response()->file($legacyPath);
        }

        abort(404);
    }
}
