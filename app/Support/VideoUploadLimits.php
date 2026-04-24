<?php

namespace App\Support;

use App\Models\SiteSetting;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;

class VideoUploadLimits
{
    public function maxDurationSeconds(): int
    {
        return max(30, (int) SiteSetting::get('member_video_max_duration_seconds', '120'));
    }

    public function maxSizeKilobytes(): int
    {
        return max(1024, (int) SiteSetting::get('member_video_max_size_kb', '51200'));
    }

    public function maxDurationMinutesLabel(): string
    {
        $seconds = $this->maxDurationSeconds();

        if ($seconds % 60 === 0) {
            return (string) ($seconds / 60);
        }

        return number_format($seconds / 60, 1, ',', '');
    }

    public function assertDurationWithinLimit(UploadedFile $file): void
    {
        $duration = $this->probeDurationSeconds($file);

        if ($duration === null) {
            return;
        }

        if ($duration > $this->maxDurationSeconds()) {
            throw ValidationException::withMessages([
                'intro_video' => 'Il video supera la durata massima consentita di '.$this->maxDurationMinutesLabel().' minuti. Riduci la durata e riprova.',
            ]);
        }
    }

    private function probeDurationSeconds(UploadedFile $file): ?int
    {
        $ffprobe = (new ExecutableFinder())->find('ffprobe');

        if (! $ffprobe) {
            return null;
        }

        $process = new Process([
            $ffprobe,
            '-v',
            'error',
            '-show_entries',
            'format=duration',
            '-of',
            'default=noprint_wrappers=1:nokey=1',
            $file->getRealPath(),
        ]);

        $process->setTimeout(30);
        $process->run();

        if (! $process->isSuccessful()) {
            return null;
        }

        $value = trim($process->getOutput());

        if (! is_numeric($value)) {
            return null;
        }

        return (int) ceil((float) $value);
    }
}
