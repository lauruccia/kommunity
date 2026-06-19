<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Sostituisce il vecchio script create_admin.php (rimosso per sicurezza).
 *
 * - Crea permessi e ruoli in modo idempotente.
 * - NON contiene password hardcoded: l'eventuale utente admin viene creato
 *   SOLO se sono valorizzate le variabili d'ambiente ADMIN_EMAIL e ADMIN_PASSWORD.
 *
 * Uso (solo in locale):
 *   ADMIN_EMAIL=tua@email.it ADMIN_PASSWORD='password-forte' php artisan db:seed --class=RolesAndAdminSeeder
 *
 * In produzione i ruoli/permessi si gestiscono via /admin (Filament);
 * questo seeder serve a inizializzare un ambiente pulito.
 */
class RolesAndAdminSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Permessi
        $permissionNames = [
            'gestire-utenti', 'assegnare-ruoli', 'assegnare-permessi',
            'gestire-eventi', 'gestire-capitoli', 'moderare-forum',
        ];
        $permissions = collect($permissionNames)
            ->mapWithKeys(fn ($p) => [$p => Permission::findOrCreate($p)]);

        // 2. Ruoli
        $roleNames = ['super-admin', 'admin-community', 'leader-capitolo', 'moderatore', 'membro', 'visitor'];
        $roles = collect($roleNames)
            ->mapWithKeys(fn ($r) => [$r => Role::findOrCreate($r)]);

        $roles['super-admin']->syncPermissions($permissions->values());
        $roles['admin-community']->syncPermissions($permissions->values());
        $roles['leader-capitolo']->syncPermissions([
            $permissions['gestire-eventi'],
            $permissions['gestire-capitoli'],
        ]);
        $roles['moderatore']->syncPermissions([$permissions['moderare-forum']]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // 3. Utente admin: SOLO se fornite credenziali via env (nessun default)
        $email = env('ADMIN_EMAIL');
        $password = env('ADMIN_PASSWORD');

        if (blank($email) || blank($password)) {
            $this->command?->warn('Ruoli/permessi creati. Nessun admin creato: imposta ADMIN_EMAIL e ADMIN_PASSWORD per crearne uno.');
            return;
        }

        $user = User::firstOrNew(['email' => $email]);
        $user->name ??= 'Admin';
        $user->password = Hash::make($password);
        $user->email_verified_at = now();
        $user->save();
        $user->syncRoles(['super-admin']);

        $this->command?->info("Admin pronto: {$email} (ruolo super-admin).");
    }
}
