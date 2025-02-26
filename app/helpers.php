<?php

function clickableLinks($text)
{
	return preg_replace('~https?://([a-z0-9-.]+)\S*~siu', '<a href="$0" target="_blank">$1</a>', e($text));
}

function clickableFullLinks($text)
{
	return preg_replace('~https?://\S*~siu', '<a href="$0" target="_blank">$0</a>', e($text));
}

function linksToDomains($text)
{
	return preg_replace('~https?://([a-z0-9-.]+)\S*~siu', '$1', $text);
}

function firstLink($text)
{
	if (preg_match('~https?://\S+~siu', $text, $match))
		return $match[0];
}

function color($text)
{
	$hash = crc32($text);

	if ($hash % 74 == 0)
		$hash = ($hash + 200) % 360;

	$hue = 31 + $hash % 247;
	$lightness = 80 + ($hash % 3 * 5);

	return "hsl({$hue}, 50%, {$lightness}%)";
}

function valueChange(string $previousValue, string $currentValue)
{
	$difference	= bcsub($currentValue, $previousValue, 9);
	$valueChange = ltrim($difference, '-');
	$increased = bccomp($difference, '0.000000000', 9) > 0;

	if (bccomp($previousValue, '0.000000000', 9) === 0 && bccomp($currentValue, '0.000000000', 9) === 0)
		$percentageChange = '0.00';
	else if (bccomp($previousValue, '0.000000000', 9) === 0)
		$percentageChange = '100.00';
	else
		$percentageChange = bcmul(bcdiv($difference, $previousValue, 9), '100.00', 2);

	$isSame = bccomp($percentageChange, '0.00', 2) === 0;

	return compact('increased', 'percentageChange', 'isSame', 'valueChange', 'currentValue', 'previousValue');
}

function hashrate($value, $precision = 2)
{
	$units = ['h/s', 'Kh/s', 'Mh/s', 'Gh/s', 'Th/s', 'Ph/s', 'Eh/s', 'Zh/s', 'Yh/s'];
	$unit  = intval(log(abs(intval($value)), 1024));

	if (array_key_exists($unit, $units)) {
		return round($value / pow(1024, $unit), $precision) . ' ' . $units[$unit];
	}

	return round($value, $precision);
}

function timestampToCarbon(int $timestamp): Carbon\Carbon
{
	return Carbon\Carbon::createFromFormat('U.u', intval(floor($timestamp / 1000)) . '.' . str_pad(($timestamp % 1000) * 1000, 6, '0', STR_PAD_LEFT), config('app.timezone'));
}

function escapeLike(string $expression): string
{
	return str_replace(['\\', '_', '%'], ['\\\\', '\\_', '\\%'], $expression);
}
