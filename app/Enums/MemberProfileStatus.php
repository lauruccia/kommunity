<?php

namespace App\Enums;

enum MemberProfileStatus: string
{
    case Draft = 'draft';
    case PendingApproval = 'pending_approval';
    case Active = 'active';
    case Suspended = 'suspended';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Bozza',
            self::PendingApproval => 'In approvazione',
            self::Active => 'Attivo',
            self::Suspended => 'Sospeso',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $case) => [$case->value => $case->label()])
            ->all();
    }
}
