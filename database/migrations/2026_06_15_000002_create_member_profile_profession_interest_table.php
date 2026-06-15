<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabella pivot: member_profile ↔ profession (professioni di interesse).
 * Usata da MemberProfile::professionsOfInterest() e ProfileController.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('member_profile_profession_interest', function (Blueprint $table) {
            $table->foreignId('member_profile_id')->constrained('member_profiles')->cascadeOnDelete();
            $table->foreignId('profession_id')->constrained('professions')->cascadeOnDelete();
            $table->primary(['member_profile_id', 'profession_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('member_profile_profession_interest');
    }
};
