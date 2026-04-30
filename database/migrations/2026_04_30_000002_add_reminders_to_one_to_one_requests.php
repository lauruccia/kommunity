<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Aggiunge:
 *   - completed_at (se mancante in produzione: la migration 2026_04_28
 *     l'aveva prevista ma in alcuni ambienti non risulta applicata)
 *   - reminder_24h_sent_at, reminder_1h_sent_at
 *   - indice su requested_at (perf reminder query)
 *
 * Idempotente: usa Schema::hasColumn per non duplicare.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('one_to_one_requests', function (Blueprint $table) {
            if (! Schema::hasColumn('one_to_one_requests', 'completed_at')) {
                $table->timestamp('completed_at')->nullable()->after('recipient_completed_at');
            }
            if (! Schema::hasColumn('one_to_one_requests', 'reminder_24h_sent_at')) {
                $table->timestamp('reminder_24h_sent_at')->nullable()->after('completed_at');
            }
            if (! Schema::hasColumn('one_to_one_requests', 'reminder_1h_sent_at')) {
                $table->timestamp('reminder_1h_sent_at')->nullable()->after('reminder_24h_sent_at');
            }
        });

        // Backfill: i 1:1 già 'completed' ricevono completed_at = updated_at
        DB::table('one_to_one_requests')
            ->where('status', 'completed')
            ->whereNull('completed_at')
            ->update(['completed_at' => DB::raw('COALESCE(updated_at, CURRENT_TIMESTAMP)')]);

        // Indice sicuro (controllo via SHOW INDEX)
        $exists = collect(DB::select('SHOW INDEX FROM one_to_one_requests'))
            ->pluck('Key_name')
            ->contains('one_to_one_requested_at_idx');

        if (! $exists) {
            Schema::table('one_to_one_requests', function (Blueprint $table) {
                $table->index('requested_at', 'one_to_one_requested_at_idx');
            });
        }
    }

    public function down(): void
    {
        Schema::table('one_to_one_requests', function (Blueprint $table) {
            $table->dropIndex('one_to_one_requested_at_idx');
            $table->dropColumn(['reminder_24h_sent_at', 'reminder_1h_sent_at']);
            // Non droppiamo completed_at: se la migration 2026_04_28
            // la considerasse "sua", il rollback altrove andrebbe in errore.
        });
    }
};
