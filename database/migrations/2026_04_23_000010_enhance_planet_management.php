<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Copertura geografica per Pianeta ─────────────────────────────────
        // Un Pianeta può coprire più regioni (molti-a-molti).
        // All'inizio un singolo Pianeta coprirà tutta Italia; crescendo se ne
        // creeranno di più con coperture regionali specifiche.
        Schema::create('chapter_region_coverage', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('chapter_id')->constrained()->cascadeOnDelete();
            $table->foreignId('region_id')->constrained()->cascadeOnDelete();
            $table->unique(['chapter_id', 'region_id']);
            $table->timestamps();
        });

        // ── Estensione chapter_join_requests ─────────────────────────────────
        Schema::table('chapter_join_requests', function (Blueprint $table): void {
            // Override admin: l'admin ha approvato superando il limite per professione
            $table->boolean('admin_override')
                ->default(false)
                ->after('waitlist_notified_at')
                ->comment('true se l\'admin ha approvato superando il limite per professione');

            // Chi ha invitato (null = richiesta spontanea dell'utente)
            $table->foreignId('invited_by_user_id')
                ->nullable()
                ->after('admin_override')
                ->constrained('users')
                ->nullOnDelete()
                ->comment('Leader o admin che ha inviato l\'invito, null se richiesta spontanea');

            // Chi ha revisionato la richiesta (admin/leader)
            $table->foreignId('reviewed_by_user_id')
                ->nullable()
                ->after('invited_by_user_id')
                ->constrained('users')
                ->nullOnDelete();

            // Quando è stata revisionata
            $table->timestamp('reviewed_at')
                ->nullable()
                ->after('reviewed_by_user_id');

            // Motivo del rifiuto (opzionale)
            $table->text('rejection_reason')
                ->nullable()
                ->after('reviewed_at');
        });
    }

    public function down(): void
    {
        Schema::table('chapter_join_requests', function (Blueprint $table): void {
            $table->dropForeign(['invited_by_user_id']);
            $table->dropForeign(['reviewed_by_user_id']);
            $table->dropColumn([
                'admin_override',
                'invited_by_user_id',
                'reviewed_by_user_id',
                'reviewed_at',
                'rejection_reason',
            ]);
        });

        Schema::dropIfExists('chapter_region_coverage');
    }
};
