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
    public function storeOptimized(UploadedFile $file, string $folder): string
    {
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
}
