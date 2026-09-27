<?php

declare(strict_types=1);

function clickableLinks(?string $text): string
{
    return preg_replace('~https?://([a-z0-9-.]+)\S*~siu', '<a href="$0" target="_blank">$1</a>', e($text));
}

function clickableFullLinks(?string $text): string
{
    return preg_replace('~https?://\S*~siu', '<a href="$0" target="_blank">$0</a>', e($text));
}

function linksToDomains(?string $text): string
{
    return preg_replace('~https?://([a-z0-9-.]+)\S*~siu', '$1', (string) $text);
}

function firstLink(?string $text): ?string
{
    if (preg_match('~https?://\S+~siu', (string) $text, $match)) {
        return $match[0];
    }

    return null;
}

function color(?string $text): string
{
    $hash = crc32((string) $text);

    if ($hash % 74 == 0) {
        $hash = ($hash + 200) % 360;
    }

    $hue = 31 + $hash % 247;
    $lightness = 80 + ($hash % 3 * 5);

    return "hsl({$hue}, 50%, {$lightness}%)";
}

function valueChange(string $previousValue, string $currentValue): array
{
    $difference = bcsub($currentValue, $previousValue, 9);
    $valueChange = ltrim($difference, '-');
    $increased = bccomp($difference, '0.000000000', 9) > 0;

    if (bccomp($previousValue, '0.000000000', 9) === 0 && bccomp($currentValue, '0.000000000', 9) === 0) {
        $percentageChange = '0.00';
    } elseif (bccomp($previousValue, '0.000000000', 9) === 0) {
        $percentageChange = '100.00';
    } else {
        $percentageChange = bcmul(bcdiv($difference, $previousValue, 9), '100.00', 2);
    }

    $isSame = bccomp($percentageChange, '0.00', 2) === 0;

    return compact('increased', 'percentageChange', 'isSame', 'valueChange', 'currentValue', 'previousValue');
}

function hashrate(int|float|string|null $value, int $precision = 2): string|float
{
    $value = (float) $value;
    $units = ['h/s', 'Kh/s', 'Mh/s', 'Gh/s', 'Th/s', 'Ph/s', 'Eh/s', 'Zh/s', 'Yh/s'];
    $unit = abs($value) < 1 ? 0 : intval(log(abs($value), 1024));

    if (array_key_exists($unit, $units)) {
        return round($value / pow(1024, $unit), $precision).' '.$units[$unit];
    }

    return round($value, $precision);
}

function timestampToCarbon(int $timestamp): Carbon\Carbon
{
    return Carbon\Carbon::createFromFormat('U.u', intval(floor($timestamp / 1000)).'.'.str_pad((string) (($timestamp % 1000) * 1000), 6, '0', STR_PAD_LEFT))->setTimezone(config('app.timezone'));
}

function escapeLike(string $expression): string
{
    return str_replace(['\\', '_', '%'], ['\\\\', '\\_', '\\%'], $expression);
}
