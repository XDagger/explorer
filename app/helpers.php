<?php

function clickable_links($text)
{
	return preg_replace('~https?://([a-z0-9-.]+)\S*~siu', '<a href="$0" target="_blank">$1</a>', e($text));
}

function clickable_full_links($text)
{
	return preg_replace('~https?://\S*~siu', '<a href="$0" target="_blank">$0</a>', e($text));
}

function links_to_domains($text)
{
	return preg_replace('~https?://([a-z0-9-.]+)\S*~siu', '$1', $text);
}

function first_link($text)
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
