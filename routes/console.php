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

// ── Promemoria 1:1: 24h e 1h prima ────────────────────────────────────────────
// Il comando è gated dalla feature flag reminders_one_to_one — se disattivata
// torna SUCCESS senza inviare nulla.
Schedule::command('kommunity:send-one-to-one-reminders')
    ->hourly()
    ->withoutOverlapping()
    ->runInBackground();

// ── Backup DB giornaliero (alle 03:15) ─────────────────────────────────────────
// Mantiene 7 backup in storage/app/backups/. Per cPanel senza scheduler PHP
// configurato, schedulare direttamente "php artisan app:db-backup" via cron
// (vedi DEPLOY_CHECKLIST_2026-04-30.md).
Schedule::command('app:db-backup --keep=7')
    ->dailyAt('03:15')
    ->withoutOverlapping()
    ->runInBackground();

// ── Queue worker: una passata ogni minuto, si ferma a coda vuota ──────────────
// Compatibile con cron cPanel. In alternativa configurare un cron diretto su
// "php artisan queue:work --stop-when-empty --max-time=55".
Schedule::command('queue:work --stop-when-empty --max-time=55 --tries=3')
    ->everyMinute()
    ->withoutOverlapping()
    ->runInBackground();

