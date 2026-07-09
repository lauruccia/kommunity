<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Candidature di ammissione a Kommunity (visitatori NON registrati).
 *
 * Flusso:
 *   - dalla card di un membro (source=card): il pianeta proposto è quello
 *     attivo del proprietario della card (presenter_user_id)
 *   - dalla homepage (source=home): il pianeta proposto è Kosmos
 *   - l'admin approva (può cambiare pianeta) → viene creato lo User,
 *     iscritto al pianeta, e riceve l'email con link "imposta password"
 *
 * NB in produzione (hosting condiviso senza terminale) la tabella va creata
 * eseguendo membership_applications.sql in phpMyAdmin.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('membership_applications', function (Blueprint $table) {
            $table->id();

            // Origine candidatura: card di un membro oppure homepage
            $table->string('source', 10)->default('home'); // card | home

            // Membro che "presenta" il candidato (proprietario della card)
            $table->foreignId('presenter_user_id')->nullable()
                ->constrained('users')->nullOnDelete();

            // Pianeta proposto (modificabile dall'admin in fase di approvazione)
            $table->foreignId('chapter_id')->nullable()
                ->constrained('chapters')->nullOnDelete();

            // Dati del candidato
            $table->string('name');
            $table->string('email');
            $table->string('phone', 30);
            $table->string('applicant_type', 10)->default('privato'); // privato | azienda
            $table->string('vat_number', 20)->nullable();             // P.IVA
            $table->string('profession')->nullable();                 // professione / attività (testo libero)
            $table->string('referrer_name')->nullable();              // "chi ti ha fatto conoscere Kommunity" (form home)
            $table->string('locale', 5)->default('it');               // lingua del candidato (per le email)

            // Revisione admin
            $table->string('status', 10)->default('pending');         // pending | approved | rejected
            $table->text('rejection_reason')->nullable();
            $table->foreignId('reviewed_by_user_id')->nullable()
                ->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();

            // Utente creato all'approvazione
            $table->foreignId('created_user_id')->nullable()
                ->constrained('users')->nullOnDelete();

            $table->timestamps();

            $table->index('status');
            $table->index('email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('membership_applications');
    }
};
