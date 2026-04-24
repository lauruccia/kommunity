<?php

namespace App\Enums;

enum OneToOneStatus: string
{
    case Pending = 'pending';
    case Accepted = 'accepted';
    case Declined = 'declined';
    case Rescheduled = 'rescheduled';
    case Cancelled = 'cancelled';
    case Completed = 'completed';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'In attesa',
            self::Accepted => 'Accettato',
            self::Declined => 'Rifiutato',
            self::Rescheduled => 'Riprogrammato',
            self::Cancelled => 'Annullato',
            self::Completed => 'Completato',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $case) => [$case->value => $case->label()])
            ->all();
    }
}
