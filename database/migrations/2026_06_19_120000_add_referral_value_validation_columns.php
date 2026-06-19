<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Referenze 2.0 — valore dichiarato dal professionista + validazione admin.
 *
 * Aggiunge le colonne necessarie a tracciare:
 *  - declared_value / declared_at  → importo dichiarato dal professionista
 *  - approved_value / approved_at / approved_by → validazione dell'admin
 *
 * Le colonne is_public / acknowledged_at vengono aggiunte solo se mancanti
 * (in alcune installazioni sono già presenti, create manualmente via SQL).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('referrals', function (Blueprint $table): void {
            if (! Schema::hasColumn('referrals', 'is_public')) {
                $table->boolean('is_public')->default(false)->after('outcome');
            }
            if (! Schema::hasColumn('referrals', 'acknowledged_at')) {
                $table->timestamp('acknowledged_at')->nullable()->after('is_public');
            }
            if (! Schema::hasColumn('referrals', 'declared_value')) {
                $table->decimal('declared_value', 12, 2)->nullable()->after('estimated_value');
            }
            if (! Schema::hasColumn('referrals', 'declared_at')) {
                $table->timestamp('declared_at')->nullable()->after('declared_value');
            }
            if (! Schema::hasColumn('referrals', 'approved_value')) {
                $table->decimal('approved_value', 12, 2)->nullable()->after('declared_at');
            }
            if (! Schema::hasColumn('referrals', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('approved_value');
            }
            if (! Schema::hasColumn('referrals', 'approved_by')) {
                $table->foreignId('approved_by')->nullable()->after('approved_at')
                    ->constrained('users')->nullOnDelete();
            }
        });

        // Normalizza gli stati storici verso il nuovo ciclo di vita.
        DB::table('referrals')
            ->whereIn('status', ['in_charge', 'contacted', 'negotiating'])
            ->update(['status' => 'in_progress']);

        DB::table('referrals')
            ->where('status', 'won')
            ->update([
                'approved_value' => DB::raw('estimated_value'),
                'approved_at'    => DB::raw('updated_at'),
                'status'         => 'confirmed',
            ]);

        DB::table('referrals')
            ->whereIn('status', ['lost', 'archived'])
            ->update(['status' => 'cancelled']);
    }

    public function down(): void
    {
        Schema::table('referrals', function (Blueprint $table): void {
            if (Schema::hasColumn('referrals', 'approved_by')) {
                $table->dropConstrainedForeignId('approved_by');
            }
            foreach (['declared_value', 'declared_at', 'approved_value', 'approved_at'] as $col) {
                if (Schema::hasColumn('referrals', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
