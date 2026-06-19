<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Ruolo globale della piattaforma, assegnabile ai membri nei Pianeti.
 *
 * I permessi sono stringhe libere che il codice controlla via
 * User::hasPlanetPermission($permission, $planetId).
 *
 * Permessi disponibili:
 *   forum.moderate   → può eliminare/bloccare post e thread nel pianeta
 *   members.invite   → può inviare inviti al pianeta
 *   members.manage   → può aggiungere/rimuovere membri dal pianeta
 *   events.manage    → può creare/modificare/cancellare eventi del pianeta
 *   content.pin      → può mettere in evidenza contenuti
 *   announcements    → può pubblicare annunci nel pianeta
 */
class PlanetRole extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'permissions',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'permissions' => 'array',
            'sort_order'  => 'integer',
        ];
    }

    // ── Boot ─────────────────────────────────────────────────────────────────

    protected static function booted(): void
    {
        static::creating(function (PlanetRole $role): void {
            if (empty($role->slug)) {
                $role->slug = Str::slug($role->name);
            }
        });
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    /** Verifica se questo ruolo include un dato permesso. */
    public function hasPermission(string $permission): bool
    {
        return in_array($permission, $this->permissions ?? [], true);
    }

    /**
     * Elenco canonico di tutti i permessi disponibili.
     * Usato come opzioni nei form Filament.
     */
    public static function availablePermissions(): array
    {
        return [
            'forum.moderate'  => 'Forum – moderare post e thread',
            'members.invite'  => 'Utenti – inviare inviti al pianeta',
            'members.manage'  => 'Utenti – aggiungere e rimuovere utenti',
            'events.manage'   => 'Eventi – creare e gestire eventi del pianeta',
            'content.pin'     => 'Contenuti – mettere in evidenza',
            'announcements'   => 'Annunci – pubblicare annunci nel pianeta',
        ];
    }
}
