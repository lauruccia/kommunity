<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Pivot tabella per selezione multipla dei settori
        Schema::create('member_profile_sector', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_profile_id')->constrained('member_profiles')->cascadeOnDelete();
            $table->foreignId('sector_id')->constrained('sectors')->cascadeOnDelete();
            $table->unique(['member_profile_id', 'sector_id']);
            $table->timestamps();
        });

        // Campo testo libero per professione personalizzata
        Schema::table('member_profiles', function (Blueprint $table) {
            $table->string('profession_other')->nullable()->after('profession_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('member_profile_sector');

        Schema::table('member_profiles', function (Blueprint $table) {
            $table->dropColumn('profession_other');
        });
    }
};
