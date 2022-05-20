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

	$hue = 31 + $hash % 247;
	$lightness = 80 + ($hash % 3 * 5);

	return "hsl({$hue}, 50%, {$lightness}%)";
}

function valueChange($previousValue, $currentValue)
{
	$difference	 = $currentValue - $previousValue;
	$valueChange = abs($difference);
	$increased	 = $difference > 0;

	if ($previousValue == 0 && $currentValue == 0)
		$percentageChange = 0.0;
	else if ($previousValue == 0)
		$percentageChange = 100;
	else
		$percentageChange = round(($difference / $previousValue) * 100, 2);

	$isSame = $percentageChange === 0.0;

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
