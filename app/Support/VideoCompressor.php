<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;

class VideoCompressor
{
    /**
     * MIME type ammessi per i video — devono corrispondere sia alla dichiarazione
     * del browser (validata da Laravel) sia ai magic bytes del file reale.
     */
    private const ALLOWED_MIME_TYPES = [
        'video/mp4',
        'video/quicktime',
        'video/webm',
    ];

    public function storeOptimized(UploadedFile $file, string $folder): string
    {
        // ── Verifica magic bytes ────────────────────────────────────────────
        // Controlla il MIME type reale leggendo la firma binaria del file
        // (non il Content-Type dichiarato dal browser, che è bypassabile).
        $this->assertMagicBytesValid($file);

        $originalPath = $file->store($folder, 'public');

        if (! $originalPath) {
            throw new \RuntimeException('Impossibile salvare il video caricato.');
        }

        $ffmpeg = (new ExecutableFinder())->find('ffmpeg');

        if (! $ffmpeg) {
            return $originalPath;
        }

        $inputPath = Storage::disk('public')->path($originalPath);
        $compressedRelativePath = trim($folder, '/').'/'.Str::uuid().'.mp4';
        $compressedPath = Storage::disk('public')->path($compressedRelativePath);

        $process = new Process([
            $ffmpeg,
            '-y',
            '-i',
            $inputPath,
            '-vf',
            'scale=1280:-2:force_original_aspect_ratio=decrease',
            '-c:v',
            'libx264',
            '-preset',
            'veryfast',
            '-crf',
            '30',
            '-movflags',
            '+faststart',
            '-c:a',
            'aac',
            '-b:a',
            '128k',
            $compressedPath,
        ]);

        $process->setTimeout(300);
        $process->run();

        if (! $process->isSuccessful() || ! is_file($compressedPath) || filesize($compressedPath) === 0) {
            Log::warning('Compressione video saltata: ffmpeg non riuscito', [
                'error' => $process->getErrorOutput(),
                'output' => $process->getOutput(),
            ]);

            @unlink($compressedPath);

            return $originalPath;
        }

        if (filesize($compressedPath) >= filesize($inputPath)) {
            @unlink($compressedPath);

            return $originalPath;
        }

        Storage::disk('public')->delete($originalPath);

        return $compressedRelativePath;
    }

    /**
     * Verifica che la firma binaria (magic bytes) del file corrisponda a un
     * formato video ammesso. Rifiuta file mascherati con estensione/MIME falsi.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    private function assertMagicBytesValid(UploadedFile $file): void
    {
        $realPath = $file->getRealPath();

        if (! $realPath || ! is_readable($realPath)) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'intro_video' => 'Impossibile leggere il file caricato.',
            ]);
        }

        $finfo    = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($realPath);

        if (! in_array($mimeType, self::ALLOWED_MIME_TYPES, true)) {
            Log::warning('Upload video rifiutato: magic bytes non corrispondono al tipo dichiarato', [
                'declared_mime' => $file->getMimeType(),
                'real_mime'     => $mimeType,
                'original_name' => $file->getClientOriginalName(),
            ]);

            throw \Illuminate\Validation\ValidationException::withMessages([
                'intro_video' => 'Il file caricato non è un video valido (mp4, mov, webm).',
            ]);
        }
    }
}
