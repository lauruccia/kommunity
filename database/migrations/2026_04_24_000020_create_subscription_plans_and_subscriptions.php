<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Piani di abbonamento (configurati dall'admin) ─────────────────
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');                          // es. "Directory Base", "Directory Pro"
            $table->string('slug')->unique();               // es. "directory-base"
            $table->text('description')->nullable();
            // Tipo piano: cosa include
            $table->string('plan_type')->default('directory_only');
            // directory_only | directory_and_page
            $table->decimal('price_monthly', 8, 2)->default(0);
            $table->decimal('price_yearly', 8, 2)->default(0);
            $table->unsignedSmallInteger('trial_days')->default(0);
            $table->json('features')->nullable();           // lista feature visibili nella card piano
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // ── Abbonamenti membri ────────────────────────────────────────────
        Schema::create('member_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('plan_id')->constrained('subscription_plans')->restrictOnDelete();

            // Stato: pending | trial | active | expired | cancelled | rejected
            $table->string('status')->default('pending');

            // Metodo pagamento: bank_transfer | card | paypal | free
            $table->string('payment_method')->nullable();
            $table->string('payment_reference')->nullable(); // es. CRO bonifico, ID transazione
            $table->text('payment_notes')->nullable();       // note libere membro al momento richiesta

            // Date ciclo di vita
            $table->timestamp('requested_at')->nullable();
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();       // null = nessuna scadenza

            // Approvazione admin
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('admin_notes')->nullable();        // note interne admin

            $table->timestamps();

            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('member_subscriptions');
        Schema::dropIfExists('subscription_plans');
    }
};
