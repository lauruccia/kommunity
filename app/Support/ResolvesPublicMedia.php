<?php

namespace App\Support;

trait ResolvesPublicMedia
{
    protected function resolvePublicMediaUrl(?string $value): ?string
    {
        if (! $value) {
            return null;
        }

        if (
            str_starts_with($value, 'http://')
            || str_starts_with($value, 'https://')
            || str_starts_with($value, '/storage/')
            || str_starts_with($value, '/media/')
        ) {
            return $value;
        }

        // Concatenazione diretta: evita che route() URL-encodi le slash (%2F)
        // causando 404 su Apache/cPanel dove AllowEncodedSlashes è off
        return '/media/' . ltrim($value, '/');
    }
}
