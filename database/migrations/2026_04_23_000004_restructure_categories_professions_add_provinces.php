<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Province (necessarie per filtro ad albero Regione → Provincia → Città)
        Schema::create('provinces', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('region_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('code', 4)->nullable()->comment('Sigla provincia (es. MI, RM)');
            $table->timestamps();
        });

        // 2. Aggiungi province_id alle città
        Schema::table('cities', function (Blueprint $table): void {
            $table->foreignId('province_id')->nullable()->after('region_id')->constrained('provinces')->nullOnDelete();
        });

        // 3. Aggiungi parent_id alle categorie (struttura ad albero)
        Schema::table('categories', function (Blueprint $table): void {
            $table->foreignId('parent_id')->nullable()->after('id')->constrained('categories')->nullOnDelete();
        });

        // 4. Pivot M2M: membro ↔ categorie (sostituisce category_id singolo)
        Schema::create('member_profile_category', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('member_profile_id')->constrained('member_profiles')->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
            $table->unique(['member_profile_id', 'category_id']);
            $table->timestamps();
        });

        // 5. Pivot M2M: membro ↔ professioni (selezione multipla)
        Schema::create('member_profile_profession', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('member_profile_id')->constrained('member_profiles')->cascadeOnDelete();
            $table->foreignId('profession_id')->constrained('professions')->cascadeOnDelete();
            $table->unique(['member_profile_id', 'profession_id']);
            $table->timestamps();
        });

        // 6. Elimina il pivot settori (settori soppressi)
        Schema::dropIfExists('member_profile_sector');

        // 7. Rimuovi sector_id da member_profiles
        Schema::table('member_profiles', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('sector_id');
        });
    }

    public function down(): void
    {
        Schema::table('member_profiles', function (Blueprint $table): void {
            $table->foreignId('sector_id')->nullable()->constrained('sectors')->nullOnDelete();
        });

        Schema::create('member_profile_sector', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('member_profile_id')->constrained('member_profiles')->cascadeOnDelete();
            $table->foreignId('sector_id')->constrained('sectors')->cascadeOnDelete();
            $table->unique(['member_profile_id', 'sector_id']);
            $table->timestamps();
        });

        Schema::dropIfExists('member_profile_profession');
        Schema::dropIfExists('member_profile_category');

        Schema::table('categories', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('parent_id');
        });

        Schema::table('cities', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('province_id');
        });

        Schema::dropIfExists('provinces');
    }
};
