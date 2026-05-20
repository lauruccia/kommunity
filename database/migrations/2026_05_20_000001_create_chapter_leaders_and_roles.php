<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── 1. Leader multipli per Pianeta ───────────────────────────────────
        // Sostituisce il singolo leader_id con una relazione molti-a-molti.
        // leader_id rimane sulla tabella chapters per compatibilità con codice
        // esistente, ma la source of truth diventa chapter_leaders.
        Schema::create('chapter_leaders', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('chapter_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unique(['chapter_id', 'user_id']);
            $table->timestamps();
        });

        // Backfill: porta il leader_id attuale in chapter_leaders
        DB::table('chapters')
            ->whereNotNull('leader_id')
            ->get(['id', 'leader_id'])
            ->each(function ($chapter): void {
                DB::table('chapter_leaders')->updateOrInsert(
                    ['chapter_id' => $chapter->id, 'user_id' => $chapter->leader_id],
                    ['created_at' => now(), 'updated_at' => now()],
                );
            });

        // ── 2. Ruoli definibili per Pianeta ─────────────────────────────────
        // L'admin può definire ruoli specifici per ogni pianeta
        // (es. "Presidente", "Tesoriere", "Segretario").
        Schema::create('chapter_roles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('chapter_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chapter_roles');
        Schema::dropIfExists('chapter_leaders');
    }
};
