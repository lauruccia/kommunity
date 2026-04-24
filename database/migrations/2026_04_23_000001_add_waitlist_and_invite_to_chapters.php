<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Aggiunge colonna "solo su invito" ai Pianeti
        Schema::table('chapters', function (Blueprint $table): void {
            $table->boolean('is_invite_only')->default(false)->after('max_members_per_profession');
        });

        // Aggiunge supporto lista d'attesa e posizione nei join request
        Schema::table('chapter_join_requests', function (Blueprint $table): void {
            $table->unsignedSmallInteger('waitlist_position')->nullable()->after('status')
                ->comment('Posizione in lista d\'attesa (1 = primo). Null se non in attesa.');
            $table->timestamp('waitlist_notified_at')->nullable()->after('waitlist_position')
                ->comment('Quando è stato notificato dello slot disponibile.');
        });
    }

    public function down(): void
    {
        Schema::table('chapter_join_requests', function (Blueprint $table): void {
            $table->dropColumn(['waitlist_position', 'waitlist_notified_at']);
        });

        Schema::table('chapters', function (Blueprint $table): void {
            $table->dropColumn('is_invite_only');
        });
    }
};
