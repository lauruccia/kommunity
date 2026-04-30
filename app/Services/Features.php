<?php

namespace App\Services;

use App\Models\FeatureFlag;
use Illuminate\Support\Facades\Cache;

/**
 * Service centralizzato per i feature flag.
 *
 * Esempi d'uso:
 *
 *   if (Features::enabled('stripe_checkout')) { ... }
 *
 *   @if(\App\Services\Features::enabled('analytics_personal'))
 *       @include('partials.dashboard-analytics')
 *   @endif
 *
 *   $payload = Features::settings('ai_matching');
 *
 * Tutti i lookup sono cachati per 30 minuti, invalidati automaticamente
 * dal model observer in App\Models\FeatureFlag.
 */
class Features
{
    /**
     * Prefisso cache key.
     */
    public const CACHE_PREFIX = 'feature_flag_';

    /**
     * TTL cache in minuti.
     */
    public const TTL_MINUTES = 30;

    /**
     * Verifica se una feature è abilitata. Resiliente al fatto che la tabella
     * potrebbe non esistere ancora (prima della migration → ritorna false).
     */
    public static function enabled(string $key, bool $default = false): bool
    {
        return (bool) Cache::remember(
            self::CACHE_PREFIX . $key,
            now()->addMinutes(self::TTL_MINUTES),
            function () use ($key, $default) {
                try {
                    $flag = FeatureFlag::query()->where('key', $key)->first();
                    return $flag ? (bool) $flag->is_enabled : $default;
                } catch (\Throwable) {
                    return $default;
                }
            }
        );
    }

    /**
     * Inverso di enabled() — utile per chiarezza in alcune view.
     */
    public static function disabled(string $key, bool $default = true): bool
    {
        return ! self::enabled($key, ! $default);
    }

    /**
     * Restituisce le settings JSON del flag (array vuoto se assente).
     */
    public static function settings(string $key): array
    {
        return Cache::remember(
            self::CACHE_PREFIX . $key . '_settings',
            now()->addMinutes(self::TTL_MINUTES),
            function () use ($key) {
                try {
                    $flag = FeatureFlag::query()->where('key', $key)->first();
                    return $flag?->settings ?? [];
                } catch (\Throwable) {
                    return [];
                }
            }
        );
    }

    /**
     * Imposta enabled=true. Utile per i seeder.
     */
    public static function enable(string $key): void
    {
        FeatureFlag::query()->where('key', $key)->update(['is_enabled' => true]);
        self::forget($key);
    }

    /**
     * Imposta enabled=false.
     */
    public static function disable(string $key): void
    {
        FeatureFlag::query()->where('key', $key)->update(['is_enabled' => false]);
        self::forget($key);
    }

    /**
     * Invalida la cache di un singolo flag.
     */
    public static function forget(string $key): void
    {
        Cache::forget(self::CACHE_PREFIX . $key);
        Cache::forget(self::CACHE_PREFIX . $key . '_settings');
    }

    /**
     * Invalida tutta la cache feature flags (forza reload completo).
     */
    public static function flush(): void
    {
        try {
            FeatureFlag::query()->pluck('key')->each(fn ($k) => self::forget($k));
        } catch (\Throwable) {
            // Tabella non ancora creata: nulla da invalidare
        }
    }
}
