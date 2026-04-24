<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case BankTransfer = 'bank_transfer';
    case Card         = 'card';
    case Paypal       = 'paypal';
    case Free         = 'free';

    public function label(): string
    {
        return match($this) {
            self::BankTransfer => 'Bonifico bancario',
            self::Card         => 'Carta di credito/debito',
            self::Paypal       => 'PayPal',
            self::Free         => 'Gratuito',
        };
    }
}
