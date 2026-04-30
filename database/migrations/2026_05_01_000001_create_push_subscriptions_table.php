<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Subscription per Web Push (RFC 8030).
 *
 * Una row per (utente, browser/device). L'endpoint è univoco a livello
 * globale — il client può cambiare endpoint quando il browser si "ribilancia"
 * con il push server, in tal caso lato client ri-soscriviamo e creiamo una
 * nuova row. Le row con revoked_at NOT NULL non ricevono più push.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('push_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Endpoint completo del push server (Mozilla, FCM, Apple…)
            $table->text('endpoint');

            // Chiavi crypto pubbliche del client (P-256 ECDH + HMAC auth)
            // base64url-encoded, lunghezza variabile ~88 + ~24 char.
            $table->string('p256dh_key', 200);
            $table->string('auth_key', 64);

            // Metadata utili per ops/UI
            $table->string('user_agent', 500)->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->unsignedInteger('failure_count')->default(0);

            $table->timestamps();

            // Hash dell'endpoint per UNIQUE — l'endpoint può essere troppo
            // lungo per un UNIQUE diretto su MySQL utf8mb4
            $table->string('endpoint_hash', 64);
            $table->unique('endpoint_hash');

            $table->index(['user_id', 'revoked_at'], 'push_subs_user_active_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('push_subscriptions');
    }
};
