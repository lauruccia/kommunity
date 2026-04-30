<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('one_to_one_requests', function (Blueprint $table) {
            $table->timestamp('reminder_24h_sent_at')->nullable()->after('completed_at');
            $table->timestamp('reminder_1h_sent_at')->nullable()->after('reminder_24h_sent_at');
            $table->index('requested_at', 'one_to_one_requested_at_idx');
        });
    }

    public function down(): void
    {
        Schema::table('one_to_one_requests', function (Blueprint $table) {
            $table->dropIndex('one_to_one_requested_at_idx');
            $table->dropColumn(['reminder_24h_sent_at', 'reminder_1h_sent_at']);
        });
    }
};
