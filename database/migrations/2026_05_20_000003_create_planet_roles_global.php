<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Ruoli globali della piattaforma ──────────────────────────────────
        // Creati dall'admin una sola volta, poi assegnabili ai membri
        // in ciascun pianeta. I permessi sono un JSON array di stringhe.
        //
        // Esempi di ruoli: "Leader", "Moderatore", "Membro semplice"
        // Esempi di permessi: "forum.moderate", "members.invite", "events.manage"
        Schema::create('planet_roles', function (Blueprint $table): void {
            $table->id();
            $table->string('name');                          // es. "Moderatore"
            $table->string('slug')->unique();                // es. "moderatore"
            $table->text('description')->nullable();
            $table->json('permissions')->nullable();         // ["forum.moderate","members.invite"]
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // ── Aggiorna chapter_member_roles per usare planet_roles ─────────────
        // Se la tabella chapter_member_roles esiste già (migration precedente
        // già eseguita), aggiorna la FK. Altrimenti la crea da zero.
        if (Schema::hasTable('chapter_member_roles')) {
            Schema::table('chapter_member_roles', function (Blueprint $table): void {
                // Rimuovi FK verso chapter_roles e riaggiungila verso planet_roles
                $table->dropForeign(['role_id']);
                $table->foreign('role_id')
                    ->references('id')->on('planet_roles')
                    ->cascadeOnDelete();
            });
        } else {
            Schema::create('chapter_member_roles', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('chapter_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('role_id')
                    ->constrained('planet_roles')
                    ->cascadeOnDelete();
                $table->timestamps();
                $table->unique(['chapter_id', 'user_id']); // un ruolo per membro per pianeta
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('chapter_member_roles')) {
            Schema::table('chapter_member_roles', function (Blueprint $table): void {
                $table->dropForeign(['role_id']);
            });
        }
        Schema::dropIfExists('planet_roles');
    }
};
