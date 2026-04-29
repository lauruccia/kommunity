<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Aggiunge cover_image alla tabella events (se non esiste già)
        if (! Schema::hasColumn('events', 'cover_image')) {
            Schema::table('events', function (Blueprint $table): void {
                $table->string('cover_image')->nullable()->after('description');
            });
        }

        // Crea la tabella event_invitations
        Schema::create('event_invitations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('invited_by')->constrained('users')->cascadeOnDelete();
            $table->string('token', 64)->unique();
            $table->string('status', 32)->default('pending');
            $table->timestamp('notified_at')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('declined_at')->nullable();
            $table->timestamps();

            $table->unique(['event_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_invitations');

        Schema::table('events', function (Blueprint $table): void {
            $table->dropColumn('cover_image');
        });
    }
};
