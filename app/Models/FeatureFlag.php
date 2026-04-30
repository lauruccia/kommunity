<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Feature flag persistito su DB. Toggle visibile in Filament admin.
 *
 * NON usare il modello direttamente nelle view: usa il facade-like service
 * \App\Services\Features::enabled('key') che è cachato.
 */
class FeatureFlag extends Model
{
    protected $fillable = [
        'key',
        'name',
        'group',
        'description',
        'is_enabled',
        'settings',
        'display_order',
    ];

    protected function casts(): array
    {
        return [
            'is_enabled'    => 'boolean',
            'settings'      => 'array',
            'display_order' => 'integer',
        ];
    }

    /**
     * Quando un flag cambia, invalida la cache del service.
     */
    protected static function booted(): void
    {
        static::saved(function (self $flag) {
            \App\Services\Features::forget($flag->key);
        });
        static::deleted(function (self $flag) {
            \App\Services\Features::forget($flag->key);
        });
    }
}
