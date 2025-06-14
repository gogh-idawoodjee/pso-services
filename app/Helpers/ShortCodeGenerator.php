<?php

namespace App\Helpers;

class ShortCodeGenerator
{
    protected const string ALPHABET = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    protected const int BASE = 62;

    /**
     * Encode an integer to a Base62 string.
     * Example: $code = ShortCodeGenerator::encode(123456);
     */
    public static function encode(int $number): string
    {
        if ($number === 0) {
            return self::ALPHABET[0];
        }

        $code = '';
        while ($number > 0) {
            $remainder = $number % self::BASE;
            $code = self::ALPHABET[$remainder] . $code;
            $number = intdiv($number, self::BASE);
        }

        return $code;
    }

    /**
     * Decode a Base62 string back to the original integer.
     * Example: $id = ShortCodeGenerator::decode($code);
     */
    public static function decode(string $code): int
    {
        $length = strlen($code);
        $number = 0;

        for ($i = 0; $i < $length; $i++) {
            $pos = strpos(self::ALPHABET, $code[$i]);
            if ($pos === false) {
                throw new \InvalidArgumentException("Invalid character '{$code[$i]}' in Base62 string.");
            }
            $number = $number * self::BASE + $pos;
        }

        return $number;
    }

    /**
     * Encode a UUID string (hex + dashes) to a Base62 string.
     * Example:
     * $uuid = '550e8400-e29b-41d4-a716-446655440000';
     * $code = ShortCodeGenerator::encodeUuid($uuid);
     */
    public static function encodeUuid(string $uuid): string
    {
        // Remove dashes
        $hex = str_replace('-', '', $uuid);

        // Convert hex string to decimal string using BCMath
        $decimal = '0';
        $length = strlen($hex);

        for ($i = 0; $i < $length; $i++) {
            $decimal = bcmul($decimal, '16');
            $decimal = bcadd($decimal, hexdec($hex[$i]));
        }

        return self::encodeBigInt($decimal);
    }

    /**
     * Decode a Base62 string back to a UUID string (with dashes).
     * Example:
     * $uuid = ShortCodeGenerator::decodeUuid($code);
     */
    public static function decodeUuid(string $code): string
    {
        // Decode base62 string to big decimal string
        $decimal = self::decodeBigInt($code);

        // Convert decimal string to hex string
        $hex = '';
        while (bccomp($decimal, '0') === 1) {
            $remainder = bcmod($decimal, '16');
            $hex = dechex((int)$remainder) . $hex;
            $decimal = bcdiv($decimal, '16', 0);
        }

        // Pad left with zeros to ensure 32 hex chars (128 bits)
        $hex = str_pad($hex, 32, '0', STR_PAD_LEFT);

        // Insert dashes to match UUID format 8-4-4-4-12
        return substr($hex, 0, 8) . '-' .
            substr($hex, 8, 4) . '-' .
            substr($hex, 12, 4) . '-' .
            substr($hex, 16, 4) . '-' .
            substr($hex, 20, 12);
    }

    /**
     * Encode a big decimal string to a Base62 string using BCMath.
     */
    protected static function encodeBigInt(string $decimal): string
    {
        if ($decimal === '0') {
            return self::ALPHABET[0];
        }

        $code = '';
        while (bccomp($decimal, '0') === 1) { // while decimal > 0
            $remainder = bcmod($decimal, (string)self::BASE);
            $code = self::ALPHABET[(int)$remainder] . $code;
            $decimal = bcdiv($decimal, (string)self::BASE, 0);
        }

        return $code;
    }

    /**
     * Decode a Base62 string to a big decimal string using BCMath.
     */
    protected static function decodeBigInt(string $code): string
    {
        $decimal = '0';
        $length = strlen($code);

        for ($i = 0; $i < $length; $i++) {
            $pos = strpos(self::ALPHABET, $code[$i]);
            if ($pos === false) {
                throw new \InvalidArgumentException("Invalid character '{$code[$i]}' in Base62 string.");
            }
            $decimal = bcmul($decimal, (string)self::BASE);
            $decimal = bcadd($decimal, (string)$pos);
        }

        return $decimal;
    }
}
