<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Assegnazione ruolo a un membro all'interno di un Pianeta.
        // Un membro ha al massimo un ruolo per Pianeta (UNIQUE su chapter+user).
        Schema::create('chapter_member_roles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('chapter_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('role_id')
                ->constrained('chapter_roles')
                ->cascadeOnDelete();
            $table->timestamps();

            // Un solo ruolo per membro per pianeta
            $table->unique(['chapter_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chapter_member_roles');
    }
};
