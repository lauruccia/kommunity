<?php

namespace App\Enums;

enum SubscriptionStatus: string
{
    case Pending   = 'pending';
    case Trial     = 'trial';
    case Active    = 'active';
    case Expired   = 'expired';
    case Cancelled = 'cancelled';
    case Rejected  = 'rejected';

    public function label(): string
    {
        return match($this) {
            self::Pending   => 'In attesa',
            self::Trial     => 'Periodo di prova',
            self::Active    => 'Attivo',
            self::Expired   => 'Scaduto',
            self::Cancelled => 'Annullato',
            self::Rejected  => 'Rifiutato',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Pending   => 'warning',
            self::Trial     => 'info',
            self::Active    => 'success',
            self::Expired   => 'gray',
            self::Cancelled => 'gray',
            self::Rejected  => 'danger',
        };
    }

    public function isAccessible(): bool
    {
        return in_array($this, [self::Trial, self::Active]);
    }
}
