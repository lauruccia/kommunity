<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Notifications\MemberWelcomeNotification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Password;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    /**
     * Dopo aver creato l'utente dall'admin, invia email con link per impostare la password.
     */
    protected function afterCreate(): void
    {
        $user = $this->getRecord();

        // Genera un token di reset password e costruisce l'URL
        $token = Password::createToken($user);
        $resetUrl = url(route('password.reset', [
            'token' => $token,
            'email' => $user->email,
        ], false));

        $user->notify(new MemberWelcomeNotification($resetUrl));
    }
}
