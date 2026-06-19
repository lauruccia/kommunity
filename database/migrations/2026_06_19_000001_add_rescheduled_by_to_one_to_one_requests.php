<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('one_to_one_requests', function (Blueprint $table): void {
            // Chi ha proposto l'ultima riprogrammazione orario.
            // Serve a far confermare la nuova proposta alla CONTROPARTE
            // (non a chi l'ha proposta), senza ulteriore attesa.
            $table->foreignId('rescheduled_by')
                ->nullable()
                ->after('status')
                ->constrained('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('one_to_one_requests', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('rescheduled_by');
        });
    }
};
