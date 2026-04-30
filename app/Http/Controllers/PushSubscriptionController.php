<?php

namespace App\Http\Controllers;

use App\Models\PushSubscription;
use App\Services\Features;
use App\Services\WebPush\WebPushService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Endpoint per il client JS:
 *   POST /push/subscribe   — registra/aggiorna una subscription
 *   DELETE /push/unsubscribe — revoca la subscription corrente del browser
 *   POST /push/test        — invia un push di test (debug, solo admin)
 *
 * Il client browser passa l'oggetto restituito da
 * pushManager.subscribe(...) serializzato in JSON.
 */
class PushSubscriptionController extends Controller
{
    public function __construct(
        protected WebPushService $webPush,
    ) {}

    /**
     * Restituisce la public key VAPID al client (necessaria per
     * pushManager.subscribe). Esposta via /push/vapid-public-key.
     */
    public function vapidPublicKey(): JsonResponse
    {
        return response()->json([
            'enabled'    => Features::enabled('pwa_push') && $this->webPush->isConfigured(),
            'public_key' => $this->webPush->isConfigured()
                ? (string) config('services.webpush.public_key')
                : null,
        ]);
    }

    /**
     * Salva una subscription. Idempotente sull'endpoint_hash:
     * se la subscription esiste già, aggiorna last_used_at.
     */
    public function subscribe(Request $request): JsonResponse
    {
        if (! Features::enabled('pwa_push')) {
            return response()->json(['error' => 'feature_disabled'], 403);
        }

        $data = $request->validate([
            'endpoint'       => ['required', 'url', 'max:1000'],
            'keys'           => ['required', 'array'],
            'keys.p256dh'    => ['required', 'string', 'max:200'],
            'keys.auth'      => ['required', 'string', 'max:64'],
        ]);

        $endpointHash = PushSubscription::hashEndpoint($data['endpoint']);

        $sub = PushSubscription::query()->updateOrCreate(
            ['endpoint_hash' => $endpointHash],
            [
                'user_id'       => $request->user()->id,
                'endpoint'      => $data['endpoint'],
                'p256dh_key'    => $data['keys']['p256dh'],
                'auth_key'      => $data['keys']['auth'],
                'user_agent'    => mb_substr((string) $request->userAgent(), 0, 500),
                'last_used_at'  => now(),
                'revoked_at'    => null,
                'failure_count' => 0,
            ]
        );

        return response()->json([
            'ok'             => true,
            'subscription_id' => $sub->id,
        ]);
    }

    /**
     * Revoca la subscription identificata dall'endpoint passato dal client.
     */
    public function unsubscribe(Request $request): JsonResponse
    {
        $data = $request->validate([
            'endpoint' => ['required', 'url', 'max:1000'],
        ]);

        $endpointHash = PushSubscription::hashEndpoint($data['endpoint']);

        $affected = PushSubscription::query()
            ->where('endpoint_hash', $endpointHash)
            ->where('user_id', $request->user()->id)
            ->update(['revoked_at' => now()]);

        return response()->json(['ok' => $affected > 0]);
    }

    /**
     * Invia un push di test all'utente loggato. Riservato agli admin per
     * debug/verifica. Non gated da feature flag (così puoi testare prima di
     * accendere il flag in produzione).
     */
    public function test(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user || ! $user->hasAnyRole(['super-admin', 'admin-community'])) {
            return response()->json(['error' => 'forbidden'], 403);
        }

        if (! $this->webPush->isConfigured()) {
            return response()->json([
                'error' => 'vapid_not_configured',
                'hint'  => 'Genera le chiavi con: php artisan kommunity:generate-vapid-keys',
            ], 412);
        }

        $delivered = $this->webPush->sendToUser($user->id, [
            'title' => '🧪 Push di test Kommunity',
            'body'  => 'Se vedi questo, Web Push funziona correttamente.',
            'icon'  => '/images/icon-192.png',
            'url'   => '/dashboard',
            'tag'   => 'kommunity-test',
        ]);

        return response()->json([
            'ok'        => $delivered > 0,
            'delivered' => $delivered,
        ]);
    }
}
