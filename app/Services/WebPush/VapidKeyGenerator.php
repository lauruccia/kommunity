<?php

namespace App\Services\WebPush;

/**
 * Generatore di coppia chiavi VAPID (EC P-256 / prime256v1).
 *
 * VAPID = Voluntary Application Server Identification (RFC 8292).
 * I browser usano queste chiavi per autenticare il nostro server presso
 * il push service (Mozilla autopush, FCM, Apple WebPush) prima di
 * accettare un push. La PUBLIC va condivisa con il client JS, la
 * PRIVATE resta nel .env del server.
 *
 * Output: base64url-encoded:
 *   - PUBLIC: 65 byte raw (uncompressed point: 0x04 || X(32) || Y(32)) → ~88 char
 *   - PRIVATE: 32 byte raw → ~43 char
 *
 * Usa solo openssl PHP nativo. Zero dipendenze.
 */
class VapidKeyGenerator
{
    /**
     * Genera una nuova coppia VAPID.
     *
     * @return array{public: string, private: string}
     */
    public static function generate(): array
    {
        $config = [
            'private_key_type' => OPENSSL_KEYTYPE_EC,
            'curve_name'       => 'prime256v1',
        ];

        $key = openssl_pkey_new($config);
        if ($key === false) {
            throw new \RuntimeException(
                'Impossibile generare la coppia EC P-256. ' .
                'Verifica che la PHP openssl extension supporti le curve EC ' .
                '(la maggior parte dei build cPanel/PHP 8.x sì).'
            );
        }

        $details = openssl_pkey_get_details($key);
        if ($details === false || ! isset($details['ec'])) {
            throw new \RuntimeException('Chiave EC senza dettagli — generazione fallita.');
        }

        // Coordinate del punto pubblico (32 byte ciascuna su P-256)
        $x = self::leftPad($details['ec']['x'] ?? '', 32);
        $y = self::leftPad($details['ec']['y'] ?? '', 32);

        // Formato uncompressed: 0x04 || X || Y → 65 byte
        $publicRaw = "\x04" . $x . $y;

        // Private key (32 byte big-endian)
        $privateRaw = self::leftPad($details['ec']['d'] ?? '', 32);

        return [
            'public'  => self::base64urlEncode($publicRaw),
            'private' => self::base64urlEncode($privateRaw),
        ];
    }

    /**
     * Padding a sinistra con zeri fino a $length byte (necessario perché
     * openssl può ritornare BN senza padding leading).
     */
    private static function leftPad(string $bin, int $length): string
    {
        $bin = ltrim($bin, "\x00");
        if (strlen($bin) > $length) {
            // Non dovrebbe mai accadere su P-256, ma safety net
            return substr($bin, -$length);
        }
        return str_pad($bin, $length, "\x00", STR_PAD_LEFT);
    }

    /**
     * Base64 URL-safe (RFC 4648 §5) senza padding =.
     */
    public static function base64urlEncode(string $bin): string
    {
        return rtrim(strtr(base64_encode($bin), '+/', '-_'), '=');
    }

    /**
     * Decode base64url, tollerante a stringhe con/senza padding.
     */
    public static function base64urlDecode(string $str): string
    {
        $remainder = strlen($str) % 4;
        if ($remainder !== 0) {
            $str .= str_repeat('=', 4 - $remainder);
        }
        return (string) base64_decode(strtr($str, '-_', '+/'), true);
    }
}
