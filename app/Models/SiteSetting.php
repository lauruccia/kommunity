<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    protected $primaryKey = 'key';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['key', 'value'];

    /**
     * Legge un'impostazione dal database.
     * Ritorna il default se la tabella non esiste ancora (es. prima della migrazione).
     */
    public static function get(string $key, ?string $default = null): ?string
    {
        try {
            return static::find($key)?->value ?? $default;
        } catch (\Throwable) {
            return $default;
        }
    }

    /**
     * Legge un'impostazione con cache (TTL 30 minuti).
     * Usare al posto di get() nei layout e nelle view caricate ad ogni request.
     */
    public static function getCached(string $key, ?string $default = null): ?string
    {
        return \Illuminate\Support\Facades\Cache::remember(
            'site_setting_' . $key,
            now()->addMinutes(30),
            fn () => static::get($key, $default)
        );
    }

    /**
     * Salva o aggiorna un'impostazione nel database e invalida la cache.
     */
    public static function set(string $key, ?string $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
        \Illuminate\Support\Facades\Cache::forget('site_setting_' . $key);
    }
}
