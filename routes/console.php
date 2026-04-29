<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ── Promemoria eventi: controlla ogni ora, invia se l'evento inizia tra ~24h ──
Schedule::command('kommunity:send-event-reminders')
    ->hourly()
    ->withoutOverlapping()
    ->runInBackground();

