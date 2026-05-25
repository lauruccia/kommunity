<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Indici di performance — 2026-05-22
 *
 * Tabelle coinvolte:
 *   - member_profiles      : active_chapter_id, (is_active, is_visible_in_directory)
 *   - one_to_one_requests  : (requester_id, status), (recipient_id, status)
 *   - forum_threads        : (chapter_id, created_at)
 *   - banner_impressions   : (banner_campaign_id, shown_at)
 *   - users                : last_seen_at
 *   - chapter_members      : (user_id, chapter_id, status)
 *
 * Ogni ALTER TABLE usa IF NOT EXISTS per essere idempotente su hosting
 * condiviso dove potrebbe non essere possibile fare rollback manuale.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── 1. member_profiles ────────────────────────────────────────────────
        // active_chapter_id: usato in quasi ogni WHERE/JOIN per filtrare pianeta.
        // Nota: foreignId() crea un index ma renameColumn lo può aver rinominato;
        // aggiungiamo con IF NOT EXISTS per sicurezza.
        $this->addIndexIfMissing(
            'member_profiles',
            'idx_mp_active_chapter_id',
            ['active_chapter_id']
        );

        // Filtro combinato directory: WHERE is_active = 1 AND is_visible_in_directory = 1
        $this->addIndexIfMissing(
            'member_profiles',
            'idx_mp_active_visible',
            ['is_active', 'is_visible_in_directory']
        );

        // ── 2. one_to_one_requests ────────────────────────────────────────────
        // Entrambe le colonne sono usate in WHERE con status in tutte le query OTO.
        // requester_id ha un FK index single-column, ma il composito è molto più
        // efficiente per le query che filtrano anche per status.
        $this->addIndexIfMissing(
            'one_to_one_requests',
            'idx_oto_requester_status',
            ['requester_id', 'status']
        );

        $this->addIndexIfMissing(
            'one_to_one_requests',
            'idx_oto_recipient_status',
            ['recipient_id', 'status']
        );

        // ── 3. forum_threads ─────────────────────────────────────────────────
        // ORDER BY is_pinned DESC, created_at DESC WHERE chapter_id = X
        $this->addIndexIfMissing(
            'forum_threads',
            'idx_ft_chapter_created',
            ['chapter_id', 'created_at']
        );

        // ── 4. banner_impressions ─────────────────────────────────────────────
        // Aggregazioni report: GROUP BY/COUNT WHERE banner_campaign_id = X AND shown_at > Y
        // shown_at ha già un index singolo; il composito copre entrambi i filtri.
        $this->addIndexIfMissing(
            'banner_impressions',
            'idx_bi_campaign_shown',
            ['banner_campaign_id', 'shown_at']
        );

        // ── 5. users ──────────────────────────────────────────────────────────
        // ORDER BY last_seen_at DESC nella directory (presenza online).
        $this->addIndexIfMissing(
            'users',
            'idx_users_last_seen_at',
            ['last_seen_at']
        );

        // ── 6. chapter_members ────────────────────────────────────────────────
        // JOIN frequentissimo: WHERE user_id = X AND chapter_id = Y AND status = 'active'
        // Esiste già unique(chapter_id, user_id) ma non copre status né query user-first.
        $this->addIndexIfMissing(
            'chapter_members',
            'idx_cm_user_chapter_status',
            ['user_id', 'chapter_id', 'status']
        );
    }

    public function down(): void
    {
        $drops = [
            ['member_profiles',     'idx_mp_active_chapter_id'],
            ['member_profiles',     'idx_mp_active_visible'],
            ['one_to_one_requests', 'idx_oto_requester_status'],
            ['one_to_one_requests', 'idx_oto_recipient_status'],
            ['forum_threads',       'idx_ft_chapter_created'],
            ['banner_impressions',  'idx_bi_campaign_shown'],
            ['users',               'idx_users_last_seen_at'],
            ['chapter_members',     'idx_cm_user_chapter_status'],
        ];

        foreach ($drops as [$table, $index]) {
            try {
                Schema::table($table, function (Blueprint $t) use ($index): void {
                    $t->dropIndex($index);
                });
            } catch (\Throwable $e) {
                // Indice non presente — ignorato
            }
        }
    }

    /**
     * Aggiunge un indice solo se non esiste già (idempotente).
     *
     * @param  string   $table
     * @param  string   $name   Nome univoco dell'indice
     * @param  string[] $cols   Colonne dell'indice
     */
    private function addIndexIfMissing(string $table, string $name, array $cols): void
    {
        $exists = DB::select("
            SELECT 1
            FROM information_schema.STATISTICS
            WHERE table_schema = DATABASE()
              AND table_name   = ?
              AND index_name   = ?
            LIMIT 1
        ", [$table, $name]);

        if (! empty($exists)) {
            return; // già presente
        }

        Schema::table($table, function (Blueprint $t) use ($name, $cols): void {
            $t->index($cols, $name);
        });
    }
};
