<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('feature_flags', function (Blueprint $table) {
            $table->id();
            $table->string('key', 64)->unique();
            $table->string('name', 160);
            $table->string('group', 64)->default('general');
            $table->text('description')->nullable();
            $table->boolean('is_enabled')->default(false);
            $table->json('settings')->nullable();
            $table->unsignedInteger('display_order')->default(100);
            $table->timestamps();

            $table->index(['group', 'is_enabled']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feature_flags');
    }
};
