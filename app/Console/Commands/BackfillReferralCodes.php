<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class BackfillReferralCodes extends Command
{
    protected $signature = 'users:backfill-referral-codes';

    protected $description = 'Genera referral_code leggibile (nome-based) per tutti gli utenti che ne sono privi';

    public function handle(): int
    {
        $users = User::query()->whereNull('referral_code')->get();

        if ($users->isEmpty()) {
            $this->info('Nessun utente senza referral_code. Tutto ok!');

            return self::SUCCESS;
        }

        $this->info("Trovati {$users->count()} utenti senza referral_code. Generazione in corso...");

        $bar = $this->output->createProgressBar($users->count());
        $bar->start();

        foreach ($users as $user) {
            $user->forceFill([
                'referral_code' => $this->uniqueReferralCode($user->name),
            ])->saveQuietly();

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('Backfill completato.');

        return self::SUCCESS;
    }

    private function uniqueReferralCode(string $name): string
    {
        $base = Str::slug($name, '');

        if ($base === '') {
            $base = 'membro';
        }

        $code = $base;
        $index = 2;

        while (User::query()->where('referral_code', $code)->exists()) {
            $code = $base . $index;
            $index++;
        }

        return $code;
    }
}
