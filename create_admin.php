<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

// 1. Crea permessi
$permissionNames = [
    'gestire-utenti', 'assegnare-ruoli', 'assegnare-permessi',
    'gestire-eventi', 'gestire-capitoli', 'moderare-forum',
];
$permissions = collect($permissionNames)
    ->mapWithKeys(fn ($p) => [$p => Permission::findOrCreate($p)]);
echo "Permessi OK." . PHP_EOL;

// 2. Crea ruoli
$roleNames = ['super-admin', 'admin-community', 'leader-capitolo', 'moderatore', 'membro', 'visitor'];
$roles = collect($roleNames)
    ->mapWithKeys(fn ($r) => [$r => Role::findOrCreate($r)]);

$roles['super-admin']->syncPermissions($permissions->values());
$roles['admin-community']->syncPermissions($permissions->values());
$roles['leader-capitolo']->syncPermissions([$permissions['gestire-eventi'], $permissions['gestire-capitoli']]);
$roles['moderatore']->syncPermissions([$permissions['moderare-forum']]);
echo "Ruoli OK." . PHP_EOL;

// 3. Aggiorna/crea utente admin
$user = User::where('email', 'admin@kommunity.test')->first();
if ($user) {
    $user->update(['password' => bcrypt('password'), 'email_verified_at' => now()]);
    echo "Utente trovato e password aggiornata." . PHP_EOL;
} else {
    $user = User::create([
        'name'              => 'Admin',
        'email'             => 'admin@kommunity.test',
        'password'          => bcrypt('password'),
        'email_verified_at' => now(),
    ]);
    echo "Utente creato." . PHP_EOL;
}

// 4. Assegna ruolo
app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
$user->syncRoles(['super-admin']);
echo "Ruolo super-admin assegnato." . PHP_EOL;

echo PHP_EOL . "Fatto! Login: admin@kommunity.test / password" . PHP_EOL;
