<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->timestamp('last_seen_at')->nullable()->after('remember_token');
            $table->boolean('show_online_status')->default(true)->after('last_seen_at');
            $table->boolean('show_read_receipts')->default(true)->after('show_online_status');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn(['last_seen_at', 'show_online_status', 'show_read_receipts']);
        });
    }
};
