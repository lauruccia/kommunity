<?php

namespace App\Services\WebPush;

use App\Models\PushSubscription;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Service alto livello per inviare Web Push notification.
 *
 * Flusso:
 *   1. Costruisce il VAPID JWT (ES256) per autenticarsi presso il push server
 *   2. Cripta il payload con WebPushEncoder (RFC 8291 - aes128gcm)
 *   3. POST verso subscription.endpoint con header VAPID + body criptato
 *   4. Su HTTP 410 Gone → marca la subscription come revocata
 *
 * VAPID keys lette da config/services.php (popolato da .env:
 * VAPID_PUBLIC_KEY, VAPID_PRIVATE_KEY, VAPID_SUBJECT).
 */
class WebPushService
{
    /**
     * TTL push (in secondi). Se il client è offline più di questo tempo,
     * il push viene scartato dal push server.
     */
    public const DEFAULT_TTL = 86400; // 24 ore

    public function __construct(
        protected ?string $vapidPublic = null,
        protected ?string $vapidPrivate = null,
        protected ?string $vapidSubject = null,
        protected ?WebPushEncoder $encoder = null,
    ) {
        $this->vapidPublic  ??= (string) config('services.webpush.public_key', env('VAPID_PUBLIC_KEY'));
        $this->vapidPrivate ??= (string) config('services.webpush.private_key', env('VAPID_PRIVATE_KEY'));
        $this->vapidSubject ??= (string) config('services.webpush.subject', env('VAPID_SUBJECT', 'mailto:info@kommunity.it'));
        $this->encoder      ??= new WebPushEncoder();
    }

    /**
     * Verifica che la configurazione VAPID sia valorizzata.
     */
    public function isConfigured(): bool
    {
        return $this->vapidPublic !== '' && $this->vapidPrivate !== '';
    }

    /**
     * Invia un push a una singola subscription. Ritorna true su 2xx,
     * false altrimenti. Su 404/410 marca la subscription come revocata.
     *
     * @param  array  $payload  Dati JSON-serializzabili (keys: title, body, icon, url, tag, ...)
     */
    public function send(PushSubscription $subscription, array $payload, int $ttl = self::DEFAULT_TTL): bool
    {
        if (! $this->isConfigured()) {
            Log::warning('WebPush: VAPID non configurato, push saltato.');
            return false;
        }

        if ($subscription->revoked_at !== null) {
            return false;
        }

        try {
            $endpoint = $subscription->endpoint;
            $origin   = $this->endpointOrigin($endpoint);

            $jwt    = $this->buildVapidJwt($origin);
            $body   = $this->encoder->encrypt(
                json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                $subscription->p256dh_key,
                $subscription->auth_key
            );

            $response = Http::withHeaders([
                    'Authorization'    => 'vapid t=' . $jwt . ', k=' . $this->vapidPublic,
                    'Content-Type'     => 'application/octet-stream',
                    'Content-Encoding' => 'aes128gcm',
                    'TTL'              => (string) $ttl,
                    'Urgency'          => 'normal',
                ])
                ->withBody($body, 'application/octet-stream')
                ->timeout(15)
                ->post($endpoint);

            if ($response->successful()) {
                $subscription->forceFill(['last_used_at' => now()])->save();
                return true;
            }

            // 404 / 410 → subscription scaduta o cancellata dall'utente
            if (in_array($response->status(), [404, 410], true)) {
                $subscription->revoke('http_' . $response->status());
                Log::info('WebPush: subscription revocata', [
                    'sub_id' => $subscription->id,
                    'status' => $response->status(),
                ]);
                return false;
            }

            // Errore transitorio: incremento failure_count
            $subscription->forceFill([
                'failure_count' => $subscription->failure_count + 1,
            ])->save();

            Log::warning('WebPush: push fallito', [
                'sub_id'   => $subscription->id,
                'status'   => $response->status(),
                'response' => mb_substr((string) $response->body(), 0, 200),
            ]);

            return false;

        } catch (\Throwable $e) {
            Log::error('WebPush: eccezione', [
                'sub_id' => $subscription->id ?? null,
                'error'  => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Invia un push a tutte le subscription attive di un utente.
     * Ritorna il numero di push consegnati con successo.
     */
    public function sendToUser(int $userId, array $payload, int $ttl = self::DEFAULT_TTL): int
    {
        $subs = PushSubscription::query()
            ->where('user_id', $userId)
            ->whereNull('revoked_at')
            ->get();

        $delivered = 0;
        foreach ($subs as $sub) {
            if ($this->send($sub, $payload, $ttl)) {
                $delivered++;
            }
        }
        return $delivered;
    }

    /**
     * Estrae origin (scheme + host + port) da un endpoint.
     */
    protected function endpointOrigin(string $endpoint): string
    {
        $parts = parse_url($endpoint);
        if (! isset($parts['scheme'], $parts['host'])) {
            throw new \InvalidArgumentException("Endpoint non valido: {$endpoint}");
        }
        $origin = $parts['scheme'] . '://' . $parts['host'];
        if (isset($parts['port'])) {
            $origin .= ':' . $parts['port'];
        }
        return $origin;
    }

    /**
     * Costruisce il JWT VAPID firmato con la nostra private key (ES256).
     */
    protected function buildVapidJwt(string $audience): string
    {
        $header = [
            'typ' => 'JWT',
            'alg' => 'ES256',
        ];
        $payload = [
            'aud' => $audience,
            'exp' => time() + (12 * 3600),  // max 24h, 12h è prudente
            'sub' => $this->vapidSubject,
        ];

        $headerB64  = VapidKeyGenerator::base64urlEncode(
            json_encode($header, JSON_UNESCAPED_SLASHES)
        );
        $payloadB64 = VapidKeyGenerator::base64urlEncode(
            json_encode($payload, JSON_UNESCAPED_SLASHES)
        );

        $toSign = $headerB64 . '.' . $payloadB64;

        // Firma ECDSA-SHA256, openssl ritorna DER → convertiamo in raw R||S 64 byte
        $derSig = '';
        $privatePem = $this->vapidPrivateToPem($this->vapidPrivate);

        $pkey = openssl_pkey_get_private($privatePem);
        if ($pkey === false) {
            throw new \RuntimeException('VAPID private key non parseabile.');
        }

        $signed = openssl_sign($toSign, $derSig, $pkey, OPENSSL_ALGO_SHA256);
        if (! $signed) {
            throw new \RuntimeException('VAPID JWT signing fallito.');
        }

        $rawSig = $this->derToRawSignature($derSig);

        return $toSign . '.' . VapidKeyGenerator::base64urlEncode($rawSig);
    }

    /**
     * Costruisce un PEM "EC PRIVATE KEY" da una private key raw 32 byte
     * (base64url-encoded come arriva da .env). Usa un wrapping DER fisso.
     */
    protected function vapidPrivateToPem(string $privateB64Url): string
    {
        $privRaw = VapidKeyGenerator::base64urlDecode($privateB64Url);
        if (strlen($privRaw) !== 32) {
            throw new \InvalidArgumentException('VAPID private key deve essere 32 byte raw.');
        }

        // Per ottenere anche il punto pubblico, deriviamo via openssl da
        // un PEM minimale che contiene solo la private key. Più semplice:
        // costruiamo un PKCS#8 DER per ecPrivateKey + prime256v1 + ourprivate
        // senza il public point (openssl lo deriva).
        //
        // PKCS#8 PrivateKeyInfo (RFC 5208):
        //   SEQUENCE {
        //     INTEGER 0
        //     AlgorithmIdentifier { id-ecPublicKey, prime256v1 }
        //     OCTET STRING { ECPrivateKey { INTEGER 1, OCTET STRING priv } }
        //   }

        $ecPrivateKey =
            "\x30\x2e" .                                                // SEQUENCE 46 byte
            "\x02\x01\x01" .                                            // INTEGER 1
            "\x04\x20" . $privRaw .                                     // OCTET STRING priv (32 byte)
            "\xa0\x07" .                                                // [0] context tag
            "\x06\x05\x2b\x81\x04\x00\x22";                             // OID secp256r1 (P-256)... wait, that's secp384

        // Correzione: per P-256 (prime256v1) l'OID è 1.2.840.10045.3.1.7
        // = 06 08 2A 86 48 CE 3D 03 01 07. Ricostruisco da capo.

        $ecPrivateKey =
            "\x30\x31" .                                                // SEQUENCE 49 byte
            "\x02\x01\x01" .                                            // INTEGER 1
            "\x04\x20" . $privRaw .                                     // OCTET STRING (32)
            "\xa0\x0a" .                                                // [0] context, len 10
            "\x06\x08\x2a\x86\x48\xce\x3d\x03\x01\x07";                 // OID prime256v1

        // Wrapping PKCS#8
        $pkcs8 =
            "\x30\x41" .                                                // SEQUENCE 65 byte
            "\x02\x01\x00" .                                            // INTEGER 0 (version)
            "\x30\x13" .                                                // SEQUENCE 19 byte (alg)
            "\x06\x07\x2a\x86\x48\xce\x3d\x02\x01" .                    // OID ecPublicKey
            "\x06\x08\x2a\x86\x48\xce\x3d\x03\x01\x07" .                // OID prime256v1
            "\x04\x27" .                                                // OCTET STRING 39 byte
            "\x30\x25" .                                                // SEQUENCE 37 byte (ECPrivateKey)
            "\x02\x01\x01" .
            "\x04\x20" . $privRaw;

        $b64 = chunk_split(base64_encode($pkcs8), 64, "\n");
        return "-----BEGIN PRIVATE KEY-----\n{$b64}-----END PRIVATE KEY-----\n";
    }

    /**
     * Converte una signature ECDSA DER in raw R||S 64 byte (per JWS ES256).
     *
     * DER: 30 LL 02 LR R 02 LS S
     * R e S sono integer big-endian, possono avere padding 0x00 leading se MSB=1.
     * Output: R(32) || S(32) — left-padded a 32 byte ciascuno.
     */
    protected function derToRawSignature(string $der): string
    {
        if (strlen($der) < 8 || $der[0] !== "\x30") {
            throw new \RuntimeException('Signature DER non valida (header).');
        }

        // Skip SEQUENCE header (0x30 + length byte)
        $offset = 2;
        if ((ord($der[1]) & 0x80) !== 0) {
            // long-form length: rare per ES256 (max ~72 byte) ma gestiamo
            $lenBytes = ord($der[1]) & 0x7f;
            $offset += $lenBytes;
        }

        // INTEGER R
        if ($der[$offset] !== "\x02") {
            throw new \RuntimeException('Signature DER non valida (R tag).');
        }
        $rLen = ord($der[$offset + 1]);
        $r    = substr($der, $offset + 2, $rLen);
        $offset += 2 + $rLen;

        // INTEGER S
        if ($der[$offset] !== "\x02") {
            throw new \RuntimeException('Signature DER non valida (S tag).');
        }
        $sLen = ord($der[$offset + 1]);
        $s    = substr($der, $offset + 2, $sLen);

        // Strip leading 0x00 e left-pad a 32 byte
        $r = ltrim($r, "\x00");
        $s = ltrim($s, "\x00");
        $r = str_pad(substr($r, -32), 32, "\x00", STR_PAD_LEFT);
        $s = str_pad(substr($s, -32), 32, "\x00", STR_PAD_LEFT);

        return $r . $s;
    }
}
