<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('advertisers', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('contact_name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('banner_campaigns', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('advertiser_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('status')->default('draft');
            $table->string('sales_package')->default('global');
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->unsignedInteger('priority')->default(0);
            $table->unsignedInteger('weight')->default(1);
            $table->decimal('price', 10, 2)->nullable();
            $table->unsignedInteger('max_impressions')->nullable();
            $table->unsignedInteger('max_clicks')->nullable();
            $table->string('target_url');
            $table->boolean('open_in_new_tab')->default(true);
            $table->string('target_mode')->default('or');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('banner_creatives', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('banner_campaign_id')->constrained()->cascadeOnDelete();
            $table->string('image_desktop');
            $table->string('image_mobile')->nullable();
            $table->string('alt_text')->nullable();
            $table->string('headline')->nullable();
            $table->string('placement_size')->default('leaderboard');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('banner_placements', function (Blueprint $table): void {
            $table->id();
            $table->string('key')->unique();
            $table->string('label');
            $table->string('section')->nullable();
            $table->unsignedInteger('desktop_width')->nullable();
            $table->unsignedInteger('desktop_height')->nullable();
            $table->unsignedInteger('mobile_width')->nullable();
            $table->unsignedInteger('mobile_height')->nullable();
            $table->unsignedInteger('max_file_size_kb')->default(300);
            $table->json('allowed_formats')->nullable();
            $table->boolean('mobile_required')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('banner_campaign_placement', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('banner_campaign_id')->constrained()->cascadeOnDelete();
            $table->foreignId('banner_placement_id')->constrained()->cascadeOnDelete();
            $table->unique(['banner_campaign_id', 'banner_placement_id'], 'banner_campaign_placement_unique');
        });

        Schema::create('banner_campaign_chapter', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('banner_campaign_id')->constrained()->cascadeOnDelete();
            $table->foreignId('chapter_id')->constrained()->cascadeOnDelete();
            $table->unique(['banner_campaign_id', 'chapter_id']);
        });

        Schema::create('banner_campaign_region', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('banner_campaign_id')->constrained()->cascadeOnDelete();
            $table->foreignId('region_id')->constrained()->cascadeOnDelete();
            $table->unique(['banner_campaign_id', 'region_id']);
        });

        Schema::create('banner_campaign_city', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('banner_campaign_id')->constrained()->cascadeOnDelete();
            $table->foreignId('city_id')->constrained()->cascadeOnDelete();
            $table->unique(['banner_campaign_id', 'city_id']);
        });

        Schema::create('banner_campaign_profession', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('banner_campaign_id')->constrained()->cascadeOnDelete();
            $table->foreignId('profession_id')->constrained()->cascadeOnDelete();
            $table->unique(['banner_campaign_id', 'profession_id'], 'banner_campaign_profession_unique');
        });

        Schema::create('banner_campaign_category', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('banner_campaign_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->unique(['banner_campaign_id', 'category_id']);
        });

        Schema::create('banner_impressions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('banner_campaign_id')->constrained()->cascadeOnDelete();
            $table->foreignId('banner_creative_id')->nullable()->constrained()->nullOnDelete();
            $table->string('placement_key');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('chapter_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('shown_at')->index();
            $table->timestamps();
        });

        Schema::create('banner_clicks', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('banner_campaign_id')->constrained()->cascadeOnDelete();
            $table->foreignId('banner_creative_id')->nullable()->constrained()->nullOnDelete();
            $table->string('placement_key');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('chapter_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('clicked_at')->index();
            $table->timestamps();
        });

        DB::table('banner_placements')->insert([
            [
                'key' => 'directory_top',
                'label' => 'Directory - fascia alta',
                'section' => 'directory',
                'desktop_width' => 1200,
                'desktop_height' => 250,
                'mobile_width' => 640,
                'mobile_height' => 320,
                'max_file_size_kb' => 300,
                'allowed_formats' => json_encode(['jpg', 'jpeg', 'png', 'webp']),
                'mobile_required' => false,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'directory_sidebar',
                'label' => 'Directory - sidebar filtri',
                'section' => 'directory',
                'desktop_width' => 600,
                'desktop_height' => 600,
                'mobile_width' => 600,
                'mobile_height' => 600,
                'max_file_size_kb' => 250,
                'allowed_formats' => json_encode(['jpg', 'jpeg', 'png', 'webp']),
                'mobile_required' => false,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('banner_clicks');
        Schema::dropIfExists('banner_impressions');
        Schema::dropIfExists('banner_campaign_category');
        Schema::dropIfExists('banner_campaign_profession');
        Schema::dropIfExists('banner_campaign_city');
        Schema::dropIfExists('banner_campaign_region');
        Schema::dropIfExists('banner_campaign_chapter');
        Schema::dropIfExists('banner_campaign_placement');
        Schema::dropIfExists('banner_placements');
        Schema::dropIfExists('banner_creatives');
        Schema::dropIfExists('banner_campaigns');
        Schema::dropIfExists('advertisers');
    }
};
