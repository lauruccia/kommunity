<?php

namespace App\Enums;

enum SubscriptionPlanType: string
{
    case DirectoryOnly      = 'directory_only';
    case DirectoryAndPage   = 'directory_and_page';

    public function label(): string
    {
        return match($this) {
            self::DirectoryOnly    => 'Solo directory',
            self::DirectoryAndPage => 'Directory + Pagina personale',
        };
    }

    public function includesPage(): bool
    {
        return $this === self::DirectoryAndPage;
    }
}
