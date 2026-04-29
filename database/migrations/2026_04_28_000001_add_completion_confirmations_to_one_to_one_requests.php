<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('one_to_one_requests', function (Blueprint $table): void {
            $table->timestamp('requester_completed_at')->nullable()->after('post_notes');
            $table->timestamp('recipient_completed_at')->nullable()->after('requester_completed_at');
            $table->timestamp('completed_at')->nullable()->after('recipient_completed_at');
        });

        DB::table('one_to_one_requests')
            ->where('status', 'completed')
            ->update([
                'requester_completed_at' => DB::raw('COALESCE(updated_at, CURRENT_TIMESTAMP)'),
                'recipient_completed_at' => DB::raw('COALESCE(updated_at, CURRENT_TIMESTAMP)'),
                'completed_at' => DB::raw('COALESCE(updated_at, CURRENT_TIMESTAMP)'),
            ]);
    }

    public function down(): void
    {
        Schema::table('one_to_one_requests', function (Blueprint $table): void {
            $table->dropColumn([
                'requester_completed_at',
                'recipient_completed_at',
                'completed_at',
            ]);
        });
    }
};
