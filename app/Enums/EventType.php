<?php

namespace App\Enums;

enum EventType: string
{
    case Online = 'online';
    case Offline = 'offline';
    case Webinar = 'webinar';
    case Networking = 'networking';
    case Training = 'training';
    case Presentation = 'presentation';
    case BusinessMatching = 'business_matching';

    public function label(): string
    {
        return match ($this) {
            self::Online => 'Online',
            self::Offline => 'Offline',
            self::Webinar => 'Webinar',
            self::Networking => 'Networking',
            self::Training => 'Formazione',
            self::Presentation => 'Presentazione',
            self::BusinessMatching => 'Business matching',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $case) => [$case->value => $case->label()])
            ->all();
    }
}
