<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('member_profiles', function (Blueprint $table): void {
            $table->string('intro_video_visibility')->default('public')->after('intro_video_url');
        });
    }

    public function down(): void
    {
        Schema::table('member_profiles', function (Blueprint $table): void {
            $table->dropColumn('intro_video_visibility');
        });
    }
};
