<?php

namespace App\Enums;

enum EventAttendanceStatus: string
{
    case Interested = 'interested';
    case NotInterested = 'not_interested';
    case Attending = 'attending';
    case Registered = 'registered';

    public function label(): string
    {
        return match ($this) {
            self::Interested => 'Mi interessa',
            self::NotInterested => 'Non mi interessa',
            self::Attending, self::Registered => 'Parteciperò',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::Interested => 'bg-sky-100 text-sky-700',
            self::NotInterested => 'bg-stone-200 text-stone-600',
            self::Attending, self::Registered => 'bg-emerald-100 text-emerald-700',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $status) => [$status->value => $status->label()])
            ->all();
    }

    public static function attendingValues(): array
    {
        return [self::Attending->value, self::Registered->value];
    }
}
