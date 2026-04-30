<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('concierge_assigned_at')->nullable()->after('email_verified_at');
            $table->foreignId('concierge_assigned_to')->nullable()->after('concierge_assigned_at')
                ->constrained('users')->nullOnDelete();
            $table->timestamp('concierge_completed_at')->nullable()->after('concierge_assigned_to');
            $table->text('concierge_notes')->nullable()->after('concierge_completed_at');
            $table->index('concierge_completed_at', 'users_concierge_completed_idx');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_concierge_completed_idx');
            $table->dropForeign(['concierge_assigned_to']);
            $table->dropColumn([
                'concierge_assigned_at',
                'concierge_assigned_to',
                'concierge_completed_at',
                'concierge_notes',
            ]);
        });
    }
};
