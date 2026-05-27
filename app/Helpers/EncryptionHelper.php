<?php

namespace App\Helpers;

use RuntimeException;

class EncryptionHelper
{
    private static string $cipher = 'AES-256-CBC';
    private static int $ivLength = 16; // For AES-256-CBC

    public static function encrypt(string $data): string
    {
        $key = self::getKey();
        $iv = random_bytes(self::$ivLength);
        $encrypted = openssl_encrypt($data, self::$cipher, $key, OPENSSL_RAW_DATA, $iv);

        $hmac = hash_hmac('sha256', $iv . $encrypted, $key, true);

        // Final format: [IV][HMAC][Encrypted]
        return base64_encode($iv . $hmac . $encrypted);
    }

    public static function decrypt(string $encryptedData): string|null
    {
        $key = self::getKey();
        $decoded = base64_decode($encryptedData, true);

        if ($decoded === false || strlen($decoded) < self::$ivLength + 32) {
            return null;
        }

        $iv = substr($decoded, 0, self::$ivLength);
        $hmac = substr($decoded, self::$ivLength, 32);
        $ciphertext = substr($decoded, self::$ivLength + 32);

        $calculatedHmac = hash_hmac('sha256', $iv . $ciphertext, $key, true);
        if (!hash_equals($hmac, $calculatedHmac)) {
            return null;
        }

        return openssl_decrypt($ciphertext, self::$cipher, $key, OPENSSL_RAW_DATA, $iv);
    }

    private static function getKey(): string
    {
        $secret = config('pso-services.settings.shared_encryption_key');

        if (empty($secret)) {
            throw new RuntimeException(
                'Missing config: pso-services.settings.shared_encryption_key. '
                . 'Set SHARED_ENCRYPTION_KEY in your .env file.'
            );
        }

        return hash('sha256', $secret, true); // 32-byte key
    }
}
