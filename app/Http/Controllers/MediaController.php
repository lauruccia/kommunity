<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class MediaController extends Controller
{
    public function show(string $path): Response
    {
        // I file fisici in public_html/media/ vengono serviti direttamente da Apache
        // tramite il .htaccess in quella directory, quindi qui arrivano solo i file
        // che sono in storage/app/public/ (disco "public" di Laravel).
        abort_unless(Storage::disk('public')->exists($path), 404);

        return response()->file(Storage::disk('public')->path($path));
    }
}
