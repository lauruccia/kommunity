<?php

namespace App\Notifications\Channels;

use App\Models\PushSubscription;
use App\Services\Features;
use App\Services\WebPush\WebPushService;
use Illuminate\Notifications\Notification;

/**
 * Channel Laravel per inviare notifiche via Web Push.
 *
 * Configurazione richiesta nelle Notification:
 *   public function via($notifiable): array {
 *       return ['mail', 'database', 'web_push'];
 *   }
 *   public function toWebPush($notifiable): array {
 *       return [
 *           'title' => '...',
 *           'body'  => '...',
 *           'icon'  => '/images/icon.png',  // opzionale
 *           'url'   => route('...'),         // su click apre questo URL
 *           'tag'   => 'unique-key',          // opzionale: dedup notifiche
 *       ];
 *   }
 *
 * Il channel è gated dal feature flag `pwa_push`. Se OFF, fa no-op.
 */
class WebPushChannel
{
    public function __construct(
        protected WebPushService $service,
    ) {}

    public function send($notifiable, Notification $notification): void
    {
        // Gate dal feature flag
        if (! Features::enabled('pwa_push')) {
            return;
        }

        if (! $this->service->isConfigured()) {
            return;
        }

        if (! method_exists($notification, 'toWebPush')) {
            return;
        }

        $payload = $notification->toWebPush($notifiable);
        if (! is_array($payload) || empty($payload)) {
            return;
        }

        // Default values
        $payload = array_merge([
            'title' => 'Kommunity',
            'body'  => '',
            'icon'  => '/images/icon-192.png',
            'badge' => '/images/badge-72.png',
            'url'   => '/',
        ], $payload);

        // Se il notifiable è un User, prendi le sue subscription attive
        $userId = $notifiable->id ?? null;
        if (! $userId) {
            return;
        }

        $subs = PushSubscription::query()
            ->where('user_id', $userId)
            ->whereNull('revoked_at')
            ->get();

        foreach ($subs as $sub) {
            $this->service->send($sub, $payload);
        }
    }
}
