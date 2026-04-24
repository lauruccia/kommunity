<?php

namespace App\Enums;

enum ReferralStatus: string
{
    case Sent = 'sent';
    case InCharge = 'in_charge';
    case Contacted = 'contacted';
    case Negotiating = 'negotiating';
    case Won = 'won';
    case Lost = 'lost';
    case Archived = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::Sent => 'Inviata',
            self::InCharge => 'Presa in carico',
            self::Contacted => 'Contattata',
            self::Negotiating => 'In trattativa',
            self::Won => 'Chiusa positiva',
            self::Lost => 'Chiusa negativa',
            self::Archived => 'Archiviata',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $case) => [$case->value => $case->label()])
            ->all();
    }
}
