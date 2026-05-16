<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── 1. Rinomina member_profiles.chapter_id → active_chapter_id ─────
        // "active_chapter_id" = pianeta nel cui contesto l'utente sta operando ora.
        // La colonna mantiene la stessa FK su chapters.id; cambia solo il nome.
        Schema::table('member_profiles', function (Blueprint $table): void {
            $table->renameColumn('chapter_id', 'active_chapter_id');
        });

        // ── 2. Backfill chapter_members dagli active_chapter_id esistenti ──
        // Ogni utente con un pianeta assegnato diventa membro "attivo" in quella
        // riga di chapter_members, se non esiste gia'.
        DB::table('member_profiles')
            ->whereNotNull('active_chapter_id')
            ->orderBy('id')
            ->chunkById(500, function ($profiles): void {
                $now = now();

                foreach ($profiles as $profile) {
                    DB::table('chapter_members')->updateOrInsert(
                        [
                            'chapter_id' => $profile->active_chapter_id,
                            'user_id' => $profile->user_id,
                        ],
                        [
                            'status' => 'active',
                            'joined_at' => $now,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ],
                    );
                }
            });

        // ── 3. Forum scopato per Pianeta ────────────────────────────────────
        // Ogni thread appartiene al pianeta in cui è stato creato.
        Schema::table('forum_threads', function (Blueprint $table): void {
            $table->foreignId('chapter_id')
                ->nullable()
                ->after('user_id')
                ->constrained()
                ->nullOnDelete();
        });

        // Backfill: assegna al thread il pianeta attivo dell'autore al momento.
        DB::statement("
            UPDATE forum_threads
            SET chapter_id = (
                SELECT mp.active_chapter_id
                FROM member_profiles mp
                WHERE mp.user_id = forum_threads.user_id
                  AND mp.active_chapter_id IS NOT NULL
                LIMIT 1
            )
            WHERE chapter_id IS NULL
              AND EXISTS (
                SELECT 1
                FROM member_profiles mp
                WHERE mp.user_id = forum_threads.user_id
                  AND mp.active_chapter_id IS NOT NULL
              )
        ");

        // ── 4. Audience degli eventi ────────────────────────────────────────
        // Permette all'admin/organizzatore di scegliere chi può vedere e
        // partecipare all'evento:
        //   'all'                     → tutti i membri
        //   'by_planet'               → solo i pianeti selezionati
        //   'by_profession'           → solo le professioni selezionate
        //   'by_planet_and_profession'→ intersezione pianeta + professione
        Schema::table('events', function (Blueprint $table): void {
            $table->string('audience_type')
                ->default('all')
                ->after('is_published')
                ->comment('all | by_planet | by_profession | by_planet_and_profession');
        });

        // ── 5. Tabella pivot: Pianeti target di un evento ───────────────────
        Schema::create('event_planet_targets', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('event_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('chapter_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->unique(['event_id', 'chapter_id']);
            $table->timestamps();
        });

        // ── 6. Tabella pivot: Professioni target di un evento ───────────────
        Schema::create('event_profession_targets', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('event_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('profession_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->unique(['event_id', 'profession_id']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_profession_targets');
        Schema::dropIfExists('event_planet_targets');

        Schema::table('events', function (Blueprint $table): void {
            $table->dropColumn('audience_type');
        });

        Schema::table('forum_threads', function (Blueprint $table): void {
            $table->dropForeign(['chapter_id']);
            $table->dropColumn('chapter_id');
        });

        Schema::table('member_profiles', function (Blueprint $table): void {
            $table->renameColumn('active_chapter_id', 'chapter_id');
        });
    }
};
