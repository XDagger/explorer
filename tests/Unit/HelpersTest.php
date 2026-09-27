<?php

declare(strict_types=1);

it('turns links into anchors showing the domain', function () {
    expect(clickableLinks('pool https://pool.example.com/stats?a=1&b=2 <b>'))
        ->toBe('pool <a href="https://pool.example.com/stats?a=1&amp;b=2" target="_blank">pool.example.com</a> &lt;b&gt;');
});

it('turns links into anchors showing the whole link', function () {
    expect(clickableFullLinks('see http://example.com/a"onmouseover="x'))
        ->toBe('see <a href="http://example.com/a&quot;onmouseover=&quot;x" target="_blank">http://example.com/a&quot;onmouseover=&quot;x</a>')
        ->and(clickableFullLinks(null))->toBe('');
});

it('shortens links to their domains', function () {
    expect(linksToDomains('mined by https://pool.example.com/x and http://other.org'))->toBe('mined by pool.example.com and other.org')
        ->and(linksToDomains(null))->toBe('');
});

it('finds the first link in a text', function (?string $text, ?string $link) {
    expect(firstLink($text))->toBe($link);
})->with([
    ['a http://one.example/x b https://two.example', 'http://one.example/x'],
    ['no link here', null],
    ['', null],
    [null, null],
]);

it('picks a stable pastel color for a text', function (?string $text) {
    $color = color($text);

    expect($color)->toBe(color($text))
        ->toMatch('/^hsl\((\d+), 50%, (80|85|90)%\)$/');

    preg_match('/^hsl\((\d+),/', $color, $match);

    expect((int) $match[1])->toBeGreaterThanOrEqual(31)->toBeLessThanOrEqual(277);
})->with(['pool.example.com', 'another pool', '', null]);

it('describes the change between two values', function (string $previous, string $current, array $expected) {
    expect(valueChange($previous, $current))->toMatchArray($expected);
})->with([
    'increase' => ['100', '150', ['increased' => true, 'percentageChange' => '50.00', 'isSame' => false, 'valueChange' => '50.000000000']],
    'decrease' => ['200', '150', ['increased' => false, 'percentageChange' => '-25.00', 'isSame' => false, 'valueChange' => '50.000000000']],
    'from zero' => ['0', '5.5', ['increased' => true, 'percentageChange' => '100.00', 'isSame' => false, 'valueChange' => '5.500000000']],
    'both zero' => ['0.000000000', '0.000000000', ['increased' => false, 'percentageChange' => '0.00', 'isSame' => true, 'valueChange' => '0.000000000']],
    'tiny change' => ['1000000', '1000000.000000001', ['increased' => true, 'percentageChange' => '0.00', 'isSame' => true, 'valueChange' => '0.000000001']],
]);

it('formats hashrates with a unit', function (int|float|string|null $value, string|float $formatted) {
    expect(hashrate($value))->toBe($formatted);
})->with([
    [0, '0 h/s'],
    [null, '0 h/s'],
    [1023, '1023 h/s'],
    [1024, '1 Kh/s'],
    [1536, '1.5 Kh/s'],
    ['2048', '2 Kh/s'],
    [12345678901, '11.5 Gh/s'],
    [1024 ** 4 * 3, '3 Th/s'],
    [1024.0 ** 9, 1024.0 ** 9],
]);

it('converts node timestamps in milliseconds to UTC dates', function (int $timestamp, string $date) {
    expect(timestampToCarbon($timestamp)->format('Y-m-d H:i:s.v e'))->toBe($date);
})->with([
    [1790000000123, '2026-09-21 14:13:20.123 UTC'],
    [5, '1970-01-01 00:00:00.005 UTC'],
    [4102444799999, '2099-12-31 23:59:59.999 UTC'],
]);

it('escapes LIKE wildcards', function () {
    expect(escapeLike('a_b%c\\d'))->toBe('a\\_b\\%c\\\\d');
});
