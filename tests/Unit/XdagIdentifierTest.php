<?php

declare(strict_types=1);

use Database\Factories\XdagIdentifier;

function base58Decode(string $encoded): string
{
    $alphabet = '123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz';
    $number = '0';

    foreach (str_split($encoded) as $character) {
        $number = bcadd(bcmul($number, '58'), (string) strpos($alphabet, $character));
    }

    $bytes = '';

    while (bccomp($number, '0') > 0) {
        $bytes = chr((int) bcmod($number, '256')).$bytes;
        $number = bcdiv($number, '256');
    }

    return str_repeat("\0", strspn($encoded, '1')).$bytes;
}

it('generates block hashes as 64 lowercase hex characters', function () {
    expect(XdagIdentifier::hash())->toMatch('/^[a-f0-9]{64}$/');
});

it('derives the block address from the reversed hash like the node does', function () {
    $hash = str_repeat('00', 8).'0102030405060708090a0b0c0d0e0f101112131415161718';

    expect(XdagIdentifier::addressOf($hash))
        ->toBe(base64_encode(strrev(hex2bin('0102030405060708090a0b0c0d0e0f101112131415161718'))))
        ->toMatch('/^[a-zA-Z0-9\/+]{32}$/');
});

it('generates block addresses the explorer accepts', function () {
    foreach (range(1, 50) as $ignored) {
        expect(XdagIdentifier::address())->toMatch('/^[a-zA-Z0-9\/+]{32}$/');
    }
});

it('generates base58check wallet addresses with a valid checksum', function () {
    foreach (range(1, 50) as $ignored) {
        $address = XdagIdentifier::walletAddress();
        $bytes = base58Decode($address);

        expect($address)->toMatch('/^[1-9A-HJ-NP-Za-km-z]{26,33}$/')
            ->and(strlen($bytes))->toBe(24)
            ->and(substr($bytes, 20))->toBe(substr(hash('sha256', hash('sha256', substr($bytes, 0, 20), true), true), 0, 4));
    }
});
