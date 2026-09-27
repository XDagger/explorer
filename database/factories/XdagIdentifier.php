<?php

declare(strict_types=1);

namespace Database\Factories;

class XdagIdentifier
{
    private const BASE58_ALPHABET = '123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz';

    public static function hash(): string
    {
        return bin2hex(random_bytes(32));
    }

    public static function addressOf(string $hash): string
    {
        return base64_encode(substr(strrev(hex2bin($hash)), 0, 24));
    }

    public static function address(): string
    {
        return self::addressOf(self::hash());
    }

    public static function walletAddress(): string
    {
        $payload = random_bytes(20);

        return self::base58($payload.substr(hash('sha256', hash('sha256', $payload, true), true), 0, 4));
    }

    private static function base58(string $bytes): string
    {
        $number = '0';

        foreach (str_split($bytes) as $byte) {
            $number = bcadd(bcmul($number, '256'), (string) ord($byte));
        }

        $encoded = '';

        while (bccomp($number, '0') > 0) {
            $encoded = self::BASE58_ALPHABET[(int) bcmod($number, '58')].$encoded;
            $number = bcdiv($number, '58');
        }

        return str_repeat('1', strspn($bytes, "\0")).$encoded;
    }
}
