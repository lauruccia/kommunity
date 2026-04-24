<?php

namespace App\Enums;

enum ContactMethod: string
{
    case Email = 'email';
    case Phone = 'phone';
    case Whatsapp = 'whatsapp';
    case Platform = 'platform';

    public function label(): string
    {
        return match ($this) {
            self::Email => 'Email',
            self::Phone => 'Telefono',
            self::Whatsapp => 'WhatsApp',
            self::Platform => 'Messaggistica interna',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $case) => [$case->value => $case->label()])
            ->all();
    }
}
