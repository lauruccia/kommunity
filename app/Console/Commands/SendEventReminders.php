<?php

namespace App\Console\Commands;

use App\Models\EventRegistration;
use App\Notifications\EventReminderNotification;
use Illuminate\Console\Command;

class SendEventReminders extends Command
{
    protected $signature   = 'kommunity:send-event-reminders';
    protected $description = 'Invia email di promemoria agli iscritti agli eventi che iniziano nelle prossime 24 ore.';

    public function handle(): int
    {
        // Finestra: eventi che iniziano tra 23h e 25h da adesso
        // La finestra di 2h garantisce che il reminder venga spedito
        // anche se il cron non gira esattamente all'orario previsto.
        $from = now()->addHours(23);
        $to   = now()->addHours(25);

        $registrations = EventRegistration::query()
            ->with(['event', 'user'])
            ->whereNull('reminder_sent_at')
            ->whereHas('event', function ($query) use ($from, $to): void {
                $query
                    ->where('is_published', true)
                    ->whereBetween('starts_at', [$from, $to]);
            })
            ->get();

        if ($registrations->isEmpty()) {
            $this->info('Nessun promemoria da inviare.');
            return self::SUCCESS;
        }

        $sent = 0;

        foreach ($registrations as $registration) {
            try {
                $registration->user->notify(
                    new EventReminderNotification($registration->event)
                );

                $registration->update(['reminder_sent_at' => now()]);
                $sent++;

                $this->line("  ✓ {$registration->user->name} → {$registration->event->title}");
            } catch (\Throwable $e) {
                $this->error("  ✗ {$registration->user->name}: {$e->getMessage()}");
            }
        }

        $this->info("Promemoria inviati: {$sent} / {$registrations->count()}");

        return self::SUCCESS;
    }
}
