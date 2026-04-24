<?php

namespace App\Enums;

enum OnepageVisibility: string
{
    case MembersOnly = 'members_only';
    case RegisteredUsers = 'registered_users';
    case PublicPreview = 'public_preview';

    public function label(): string
    {
        return match ($this) {
            self::MembersOnly => 'Solo membri',
            self::RegisteredUsers => 'Utenti registrati',
            self::PublicPreview => 'Anteprima pubblica',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $case) => [$case->value => $case->label()])
            ->all();
    }
}
