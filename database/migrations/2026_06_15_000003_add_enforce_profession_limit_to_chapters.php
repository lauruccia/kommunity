<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Aggiunge enforce_profession_limit alla tabella chapters.
 * Colonna presente in produzione ma mancante in locale — retrocompatibile.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chapters', function (Blueprint $table) {
            if (! Schema::hasColumn('chapters', 'enforce_profession_limit')) {
                $table->boolean('enforce_profession_limit')->default(true)->after('max_members_per_profession');
            }
        });
    }

    public function down(): void
    {
        Schema::table('chapters', function (Blueprint $table) {
            if (Schema::hasColumn('chapters', 'enforce_profession_limit')) {
                $table->dropColumn('enforce_profession_limit');
            }
        });
    }
};
