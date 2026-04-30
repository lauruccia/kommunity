<?php

namespace App\Console\Commands;

use App\Services\WebPush\VapidKeyGenerator;
use Illuminate\Console\Command;

/**
 * Genera una coppia VAPID per Web Push e stampa i valori da incollare in .env.
 *
 * Uso (cron one-shot da cPanel):
 *     /usr/local/bin/php /home/USER/public_html/artisan kommunity:generate-vapid-keys
 *
 * Sicurezza: il comando NON scrive su .env automaticamente. Stampa le chiavi
 * a stdout e le salva in storage/logs/vapid-YYYYMMDD-HHMMSS.txt — devi tu
 * incollarle manualmente in .env tramite FileManager.
 *
 * Generale UNA SOLA VOLTA per la vita del progetto. Se rigeneri le chiavi,
 * tutte le subscription esistenti diventano invalide e gli utenti devono
 * ri-acconsentire.
 */
class GenerateVapidKeys extends Command
{
    protected $signature   = 'kommunity:generate-vapid-keys
                              {--save : Salva anche su storage/logs/}';
    protected $description = 'Genera coppia VAPID (EC P-256) per Web Push.';

    public function handle(): int
    {
        $this->info('Generazione coppia VAPID (EC P-256)...');

        try {
            $keys = VapidKeyGenerator::generate();
        } catch (\Throwable $e) {
            $this->error('Errore: ' . $e->getMessage());
            return self::FAILURE;
        }

        $this->newLine();
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->line('  Aggiungi questi valori al tuo file <comment>.env</comment>:');
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->newLine();
        $this->line("VAPID_PUBLIC_KEY={$keys['public']}");
        $this->line("VAPID_PRIVATE_KEY={$keys['private']}");
        $this->line("VAPID_SUBJECT=mailto:info@kommunity.it");
        $this->newLine();
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->newLine();

        $this->warn('IMPORTANTE:');
        $this->warn('  • La PRIVATE non deve mai uscire dal server.');
        $this->warn('  • La PUBLIC verrà esposta automaticamente al JS del client.');
        $this->warn('  • Genera UNA SOLA VOLTA — rigenerare invalida tutte le subscription esistenti.');
        $this->newLine();

        // Salva su file per recovery (filtrabile da admin via FileManager)
        if ($this->option('save')) {
            $path = storage_path('logs/vapid-' . now()->format('Ymd-His') . '.txt');
            file_put_contents(
                $path,
                "VAPID_PUBLIC_KEY={$keys['public']}\n" .
                "VAPID_PRIVATE_KEY={$keys['private']}\n" .
                "VAPID_SUBJECT=mailto:info@kommunity.it\n" .
                "\nGenerato: " . now()->toDateTimeString() . "\n"
            );
            chmod($path, 0600);
            $this->info("Salvato anche su: {$path}");
            $this->warn('Ricorda di eliminare il file dopo aver copiato i valori in .env.');
        }

        return self::SUCCESS;
    }
}
