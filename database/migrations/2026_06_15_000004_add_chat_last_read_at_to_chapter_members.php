<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Aggiunge chat_last_read_at a chapter_members.
 * Traccia l'ultima volta che il membro ha aperto la chat del pianeta.
 * Usato per calcolare il badge "messaggi non letti" sul FAB.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chapter_members', function (Blueprint $table) {
            $table->timestamp('chat_last_read_at')->nullable()->after('joined_at');
        });
    }

    public function down(): void
    {
        Schema::table('chapter_members', function (Blueprint $table) {
            $table->dropColumn('chat_last_read_at');
        });
    }
};
