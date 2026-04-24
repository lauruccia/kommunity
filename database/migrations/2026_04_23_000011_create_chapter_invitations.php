<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabella degli inviti al Pianeta.
     *
     * Flusso:
     *  1. Il leader (o admin) crea un invito indicando email + pianeta.
     *  2. Il sistema genera un token univoco e invia un'email con il link /invita/{token}.
     *  3. L'utente clicca il link → viene reindirizzato a /register?invite={token}.
     *  4. Dopo la registrazione, il token viene rilevato e l'utente è assegnato
     *     automaticamente al Pianeta senza necessità di approvazione.
     *  5. Lo status passa a 'accepted'; accepted_at e accepted_by_user_id vengono compilati.
     */
    public function up(): void
    {
        Schema::create('chapter_invitations', function (Blueprint $table): void {
            $table->id();

            // Pianeta di destinazione
            $table->foreignId('chapter_id')
                ->constrained()
                ->cascadeOnDelete();

            // Chi ha emesso l'invito (leader o admin)
            $table->foreignId('invited_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // Email dell'invitato (può non essere ancora registrato)
            $table->string('email');

            // Token univoco da includere nel link di invito
            $table->string('token')->unique();

            // Stato: pending → accepted | expired | revoked
            $table->string('status')->default('pending')
                ->comment('pending, accepted, expired, revoked');

            // Messaggio personalizzato del leader (opzionale)
            $table->text('message')->nullable();

            // Scadenza del link (null = nessuna scadenza)
            $table->timestamp('expires_at')->nullable();

            // Quando/da chi è stato accettato
            $table->timestamp('accepted_at')->nullable();
            $table->foreignId('accepted_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chapter_invitations');
    }
};
