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
            ['key' => 'directory_top', 'label' => 'Directory - fascia alta', 'section' => 'directory', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'directory_sidebar', 'label' => 'Directory - sidebar filtri', 'section' => 'directory', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
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
