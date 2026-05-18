<?php

namespace App\Models;

use App\Support\ResolvesPublicMedia;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class BannerCreative extends Model
{
    use ResolvesPublicMedia;

    protected $fillable = [
        'banner_campaign_id',
        'image_desktop',
        'image_mobile',
        'alt_text',
        'headline',
        'placement_size',
        'is_active',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    protected static function booted(): void
    {
        static::saving(function (BannerCreative $creative): void {
            $creative->validateAgainstPlacements();
        });
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(BannerCampaign::class, 'banner_campaign_id');
    }

    public function desktopImageUrl(): ?string
    {
        return $this->resolvePublicMediaUrl($this->image_desktop);
    }

    public function mobileImageUrl(): ?string
    {
        return $this->resolvePublicMediaUrl($this->image_mobile);
    }

    private function validateAgainstPlacements(): void
    {
        $campaign = $this->campaign()->with('placements')->first();

        if (! $campaign || $campaign->placements->isEmpty()) {
            return;
        }

        $errors = [];
        $this->validateImageFile('image_desktop', $this->image_desktop, $campaign->placements, $errors);

        if ($campaign->placements->contains(fn (BannerPlacement $placement): bool => $placement->mobile_required) && ! $this->image_mobile) {
            $errors['image_mobile'][] = 'Questo placement richiede una creativita mobile.';
        }

        if ($this->image_mobile) {
            $this->validateImageFile('image_mobile', $this->image_mobile, $campaign->placements, $errors, mobile: true);
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }
    }

    /**
     * @param  Collection<int, BannerPlacement>  $placements
     * @param  array<string, array<int, string>>  $errors
     */
    private function validateImageFile(string $field, ?string $path, Collection $placements, array &$errors, bool $mobile = false): void
    {
        if (! $path || ! Storage::disk('public')->exists($path)) {
            return;
        }

        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $sizeKb = (int) ceil(Storage::disk('public')->size($path) / 1024);
        $imageSize = @getimagesize(Storage::disk('public')->path($path));

        foreach ($placements as $placement) {
            $allowedFormats = $placement->allowed_formats ?: ['jpg', 'jpeg', 'png', 'webp'];

            if (! in_array($extension, $allowedFormats, true)) {
                $errors[$field][] = 'Formato non valido per "' . $placement->label . '". Formati ammessi: ' . implode(', ', $allowedFormats) . '.';
            }

            if ($placement->max_file_size_kb && $sizeKb > $placement->max_file_size_kb) {
                $errors[$field][] = 'File troppo pesante per "' . $placement->label . '": massimo ' . $placement->max_file_size_kb . ' KB.';
            }

            if (! $imageSize) {
                continue;
            }

            [$width, $height] = $imageSize;
            $expectedRatio = $mobile ? $placement->mobileRatio() : $placement->desktopRatio();
            $expectedWidth = $mobile ? $placement->mobile_width : $placement->desktop_width;
            $expectedHeight = $mobile ? $placement->mobile_height : $placement->desktop_height;

            if ($expectedWidth && $width < $expectedWidth) {
                $errors[$field][] = 'Larghezza insufficiente per "' . $placement->label . '": minimo ' . $expectedWidth . ' px.';
            }

            if ($expectedHeight && $height < $expectedHeight) {
                $errors[$field][] = 'Altezza insufficiente per "' . $placement->label . '": minimo ' . $expectedHeight . ' px.';
            }

            if ($expectedRatio && abs(($width / max($height, 1)) - $expectedRatio) > ($expectedRatio * 0.02)) {
                $expectedFormat = $expectedWidth && $expectedHeight ? $expectedWidth . 'x' . $expectedHeight : 'proporzioni richieste';
                $errors[$field][] = 'Proporzioni non adatte per "' . $placement->label . '". Formato richiesto: ' . $expectedFormat . '.';
            }
        }
    }
}
