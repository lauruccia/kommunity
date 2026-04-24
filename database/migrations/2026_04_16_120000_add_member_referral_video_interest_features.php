<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_interest_types', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('company_interest_type_member_profile', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('member_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('company_interest_type_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['member_profile_id', 'company_interest_type_id'], 'company_interest_profile_unique');
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->string('referral_code', 32)->nullable()->unique()->after('password');
            $table->foreignId('invited_by_user_id')->nullable()->after('referral_code')->constrained('users')->nullOnDelete();
            $table->string('invited_by_name')->nullable()->after('invited_by_user_id');
        });

        Schema::table('member_profiles', function (Blueprint $table): void {
            $table->string('intro_video')->nullable()->after('logo');
            $table->unsignedTinyInteger('intro_video_duration_minutes')->nullable()->after('intro_video');
        });

        Schema::table('chapters', function (Blueprint $table): void {
            $table->unsignedTinyInteger('max_members_per_profession')->default(3)->after('cover_image');
        });
    }

    public function down(): void
    {
        Schema::table('chapters', function (Blueprint $table): void {
            $table->dropColumn('max_members_per_profession');
        });

        Schema::table('member_profiles', function (Blueprint $table): void {
            $table->dropColumn(['intro_video', 'intro_video_duration_minutes']);
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('invited_by_user_id');
            $table->dropUnique(['referral_code']);
            $table->dropColumn(['referral_code', 'invited_by_name']);
        });

        Schema::dropIfExists('company_interest_type_member_profile');
        Schema::dropIfExists('company_interest_types');
    }
};
