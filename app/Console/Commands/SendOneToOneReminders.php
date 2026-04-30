<?php

namespace App\Console\Commands;

use App\Enums\OneToOneStatus;
use App\Models\OneToOneRequest;
use App\Notifications\OneToOneReminderNotification;
use App\Services\Features;
use Illuminate\Console\Command;

/**
 * Invia promemoria 24h e 1h prima dei 1:1 confermati.
 *
 * Schedulato hourly da routes/console.php. Usa due finestre temporali con
 * margine: il margine garantisce che, anche se il cron salta un'esecuzione,
 * la notifica parta nella finestra successiva.
 *
 * - 24h: requested_at compreso fra now+23h e now+25h, reminder_24h_sent_at NULL
 * - 1h:  requested_at compreso fra now+30min e now+90min, reminder_1h_sent_at NULL
 *
 * Gated dal feature flag `reminders_one_to_one`.
 */
class SendOneToOneReminders extends Command
{
    protected $signature   = 'kommunity:send-one-to-one-reminders
                              {--dry-run : Mostra cosa sarebbe inviato senza notificare}';
    protected $description = 'Invia reminder 24h e 1h prima dei 1:1 confermati.';

    public function handle(): int
    {
        if (! Features::enabled('reminders_one_to_one')) {
            $this->info('Feature flag "reminders_one_to_one" disattivata. Skip.');
            return self::SUCCESS;
        }

        $dryRun = (bool) $this->option('dry-run');
        $sent24 = $this->processWindow('24h', now()->addHours(23), now()->addHours(25), 'reminder_24h_sent_at', $dryRun);
        $sent1  = $this->processWindow('1h',  now()->addMinutes(30), now()->addMinutes(90), 'reminder_1h_sent_at', $dryRun);

        $this->info("Reminder inviati — 24h: {$sent24} | 1h: {$sent1}" . ($dryRun ? ' (dry-run)' : ''));

        return self::SUCCESS;
    }

    /**
     * @param  string  $window  '24h' | '1h'
     */
    protected function processWindow(string $window, \Illuminate\Support\Carbon $from, \Illuminate\Support\Carbon $to, string $sentColumn, bool $dryRun): int
    {
        $requests = OneToOneRequest::query()
            ->with(['requester', 'recipient'])
            ->whereIn('status', [OneToOneStatus::Accepted->value, OneToOneStatus::Rescheduled->value])
            ->whereBetween('requested_at', [$from, $to])
            ->whereNull($sentColumn)
            ->get();

        if ($requests->isEmpty()) {
            return 0;
        }

        $count = 0;
        foreach ($requests as $request) {
            try {
                if (! $dryRun) {
                    $request->requester?->notify(new OneToOneReminderNotification($request, $window));
                    $request->recipient?->notify(new OneToOneReminderNotification($request, $window));
                    $request->forceFill([$sentColumn => now()])->save();
                }
                $this->line("  ✓ [{$window}] #{$request->id} → {$request->requester?->name} / {$request->recipient?->name}");
                $count++;
            } catch (\Throwable $e) {
                $this->error("  ✗ [{$window}] #{$request->id}: {$e->getMessage()}");
            }
        }

        return $count;
    }
}
