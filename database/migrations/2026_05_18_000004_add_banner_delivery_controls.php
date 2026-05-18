<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('banner_placements', 'desktop_width')) {
            Schema::table('banner_placements', function (Blueprint $table): void {
                $table->unsignedInteger('desktop_width')->nullable()->after('section');
                $table->unsignedInteger('desktop_height')->nullable()->after('desktop_width');
                $table->unsignedInteger('mobile_width')->nullable()->after('desktop_height');
                $table->unsignedInteger('mobile_height')->nullable()->after('mobile_width');
                $table->unsignedInteger('max_file_size_kb')->default(300)->after('mobile_height');
                $table->json('allowed_formats')->nullable()->after('max_file_size_kb');
                $table->boolean('mobile_required')->default(false)->after('allowed_formats');
            });
        }

        if (! Schema::hasColumn('banner_campaigns', 'weight')) {
            Schema::table('banner_campaigns', function (Blueprint $table): void {
                $table->unsignedInteger('weight')->default(1)->after('priority');
            });
        }

        DB::table('banner_placements')
            ->where('key', 'directory_top')
            ->update([
                'desktop_width' => 1200,
                'desktop_height' => 250,
                'mobile_width' => 640,
                'mobile_height' => 320,
                'max_file_size_kb' => 300,
                'allowed_formats' => json_encode(['jpg', 'jpeg', 'png', 'webp']),
                'mobile_required' => false,
            ]);

        DB::table('banner_placements')
            ->where('key', 'directory_sidebar')
            ->update([
                'desktop_width' => 600,
                'desktop_height' => 600,
                'mobile_width' => 600,
                'mobile_height' => 600,
                'max_file_size_kb' => 250,
                'allowed_formats' => json_encode(['jpg', 'jpeg', 'png', 'webp']),
                'mobile_required' => false,
            ]);
    }

    public function down(): void
    {
        if (Schema::hasColumn('banner_campaigns', 'weight')) {
            Schema::table('banner_campaigns', function (Blueprint $table): void {
                $table->dropColumn('weight');
            });
        }

        if (Schema::hasColumn('banner_placements', 'desktop_width')) {
            Schema::table('banner_placements', function (Blueprint $table): void {
                $table->dropColumn([
                    'desktop_width',
                    'desktop_height',
                    'mobile_width',
                    'mobile_height',
                    'max_file_size_kb',
                    'allowed_formats',
                    'mobile_required',
                ]);
            });
        }
    }
};
