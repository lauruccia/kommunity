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
     * Salva o aggiorna un'impostazione nel database.
     */
    public static function set(string $key, ?string $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
    }
}
