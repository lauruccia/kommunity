<?php

namespace App\Services\WebPush;

/**
 * Encoder per Web Push payload encrypted (RFC 8291 - aes128gcm).
 *
 * Implementa lo schema "Encrypted Content-Encoding: aes128gcm" usato dai
 * push server moderni (FCM, Mozilla autopush, Apple WebPush). Si appoggia
 * solo a estensioni PHP standard (openssl + hash). Zero dipendenze esterne.
 *
 * Riferimenti:
 *   - RFC 8188: Encrypted Content-Encoding for HTTP
 *   - RFC 8291: Message Encryption for Web Push
 *   - RFC 7515 / 7518: JWS / JWA (per VAPID firma)
 */
class WebPushEncoder
{
    /**
     * Lunghezza del record (record size). 4096 byte è il default raccomandato
     * dalla RFC e sufficiente per qualunque payload Web Push (limit 4 KB).
     */
    private const RECORD_SIZE = 4096;

    /**
     * Cripta il payload secondo aes128gcm.
     *
     * @param  string  $payload         Plaintext (es. JSON serializzato della notifica)
     * @param  string  $uaPublicB64Url  Public key del subscriber (base64url)
     * @param  string  $authSecretB64Url Auth secret del subscriber (base64url, 16 byte raw)
     * @return string                   Body binario da inviare con Content-Encoding: aes128gcm
     */
    public function encrypt(string $payload, string $uaPublicB64Url, string $authSecretB64Url): string
    {
        $uaPublicRaw = VapidKeyGenerator::base64urlDecode($uaPublicB64Url);
        $authSecret  = VapidKeyGenerator::base64urlDecode($authSecretB64Url);

        if (strlen($uaPublicRaw) !== 65 || $uaPublicRaw[0] !== "\x04") {
            throw new \InvalidArgumentException(
                'Subscription public key non valida: attesi 65 byte uncompressed (0x04 || X || Y).'
            );
        }
        if (strlen($authSecret) !== 16) {
            throw new \InvalidArgumentException(
                'Subscription auth secret non valido: attesi 16 byte.'
            );
        }
        if (strlen($payload) > self::RECORD_SIZE - 17) {
            // 16 (tag GCM) + 1 (delimiter 0x02)
            throw new \InvalidArgumentException(
                'Payload troppo lungo per record_size 4096 (max ~4079 byte).'
            );
        }

        // 1) Genera coppia ephemeral server (P-256)
        $ephemeral = $this->generateEphemeralKey();

        // 2) ECDH: server_private × ua_public → shared secret 32 byte
        $sharedSecret = $this->ecdhSharedSecret($ephemeral['pkey'], $uaPublicRaw);

        // 3) HKDF Extract con auth_secret come salt e shared come IKM
        //    PRK_key = HMAC-SHA-256(auth_secret, shared_secret)
        $prkKey = hash_hmac('sha256', $sharedSecret, $authSecret, true);

        // 4) HKDF Expand-only per derivare l'IKM "Web Push" (RFC 8291 §3.4)
        //
        // NOTA IMPORTANTE: PHP hash_hkdf() fa Extract+Expand insieme — non si
        // può ottenere solo Expand passando un PRK già calcolato. Per questo
        // implementiamo Expand manualmente (RFC 5869 §2.3) qui sotto.
        $keyInfo = 'WebPush: info' . "\x00" . $uaPublicRaw . $ephemeral['public'];
        $ikm     = self::hkdfExpand($prkKey, $keyInfo, 32);

        // 5) HKDF Extract con salt random e IKM appena calcolato
        $salt = random_bytes(16);
        $prk  = hash_hmac('sha256', $ikm, $salt, true);

        // 6) Deriva CEK (16 byte) e NONCE (12 byte) — Expand only
        $cek   = self::hkdfExpand($prk, 'Content-Encoding: aes128gcm' . "\x00", 16);
        $nonce = self::hkdfExpand($prk, 'Content-Encoding: nonce' . "\x00", 12);

        // 7) Plaintext = payload || 0x02 (delimiter di fine record, no padding)
        $plaintext = $payload . "\x02";

        // 8) AES-128-GCM
        $tag = '';
        $ciphertext = openssl_encrypt(
            $plaintext,
            'aes-128-gcm',
            $cek,
            OPENSSL_RAW_DATA,
            $nonce,
            $tag
        );

        if ($ciphertext === false) {
            throw new \RuntimeException('AES-128-GCM encrypt fallito: ' . openssl_error_string());
        }

        // 9) Body finale (RFC 8188 §2.1):
        //    salt(16) || rs(4 BE uint32) || idlen(1) || keyid || ciphertext+tag
        return $salt
            . pack('N', self::RECORD_SIZE)
            . chr(65)
            . $ephemeral['public']
            . $ciphertext
            . $tag;
    }

    /**
     * @return array{pkey: \OpenSSLAsymmetricKey, public: string}
     */
    private function generateEphemeralKey(): array
    {
        $pkey = openssl_pkey_new([
            'private_key_type' => OPENSSL_KEYTYPE_EC,
            'curve_name'       => 'prime256v1',
        ]);

        if ($pkey === false) {
            throw new \RuntimeException(
                'Impossibile generare keypair ephemeral (openssl_pkey_new). ' .
                'La PHP openssl extension potrebbe non avere curve EC abilitate.'
            );
        }

        $details = openssl_pkey_get_details($pkey);
        $x = self::leftPad($details['ec']['x'] ?? '', 32);
        $y = self::leftPad($details['ec']['y'] ?? '', 32);

        return [
            'pkey'   => $pkey,
            'public' => "\x04" . $x . $y,
        ];
    }

    /**
     * ECDH shared secret tra server_private e peer_public_uncompressed.
     */
    private function ecdhSharedSecret(\OpenSSLAsymmetricKey $serverPrivate, string $peerPublicRaw): string
    {
        // openssl_pkey_derive richiede il peer come PEM-encoded PUBLIC KEY.
        // Costruiamo il PEM da zero a partire dalle coordinate raw.
        $peerPem = self::ecPointToPem($peerPublicRaw);

        $peer = openssl_pkey_get_public($peerPem);
        if ($peer === false) {
            throw new \RuntimeException('Subscription public key non parseabile: ' . openssl_error_string());
        }

        $secret = openssl_pkey_derive($peer, $serverPrivate, 32);
        if ($secret === false) {
            throw new \RuntimeException('ECDH derive fallito: ' . openssl_error_string());
        }

        return $secret;
    }

    /**
     * Costruisce un PEM "PUBLIC KEY" (SubjectPublicKeyInfo) da un punto EC raw
     * uncompressed (65 byte: 0x04 || X || Y) su curva P-256.
     *
     * Si tratta di concatenare un prefisso DER fisso che descrive
     * "ecPublicKey + prime256v1" e poi BIT STRING contenente il punto raw.
     */
    private static function ecPointToPem(string $rawPoint): string
    {
        if (strlen($rawPoint) !== 65 || $rawPoint[0] !== "\x04") {
            throw new \InvalidArgumentException('EC point deve essere 65 byte uncompressed.');
        }

        // Prefisso DER per SubjectPublicKeyInfo con OID id-ecPublicKey + curva prime256v1
        // (è una stringa DER fissa di 26 byte, segue la BIT STRING).
        $derPrefix = hex2bin(
            '3059' .                                    // SEQUENCE (0x59 = 89 byte)
            '301306072a8648ce3d020106082a8648ce3d030107' . // SEQUENCE: OIDs
            '034200'                                    // BIT STRING (66 byte, unused 0)
        );

        $der = $derPrefix . $rawPoint;
        $b64 = chunk_split(base64_encode($der), 64, "\n");

        return "-----BEGIN PUBLIC KEY-----\n{$b64}-----END PUBLIC KEY-----\n";
    }

    /**
     * HKDF-Expand SOLO Expand (RFC 5869 §2.3).
     *
     * PHP `hash_hkdf()` esegue Extract+Expand insieme: non si può ottenere
     * solo la fase Expand passando un PRK già calcolato. Per WebPush ci serve
     * derivare CEK / NONCE / IKM partendo da PRK già estratti, quindi
     * implementiamo Expand a mano.
     *
     *     T(0) = empty
     *     T(i) = HMAC-SHA256(PRK, T(i-1) || info || i)
     *     OKM  = T(1) || T(2) || ... troncato a $length byte
     *
     * @param  string  $prk     PRK già estratto (32 byte per SHA-256)
     * @param  string  $info    Info string (incluso eventuale \x00 di chiusura)
     * @param  int     $length  Lunghezza desiderata (≤ 255 * hash_size)
     */
    public static function hkdfExpand(string $prk, string $info, int $length): string
    {
        $hashLen = 32; // SHA-256
        if ($length > 255 * $hashLen) {
            throw new \InvalidArgumentException('HKDF expand: length troppo grande.');
        }

        $n      = (int) ceil($length / $hashLen);
        $t      = '';
        $output = '';
        for ($i = 1; $i <= $n; $i++) {
            $t = hash_hmac('sha256', $t . $info . chr($i), $prk, true);
            $output .= $t;
        }

        return substr($output, 0, $length);
    }

    /**
     * Padding sinistro a $length byte.
     */
    private static function leftPad(string $bin, int $length): string
    {
        $bin = ltrim($bin, "\x00");
        return str_pad(substr($bin, -$length), $length, "\x00", STR_PAD_LEFT);
    }
}
