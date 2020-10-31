<?php
namespace App\Xdag\Block\Validation;

class Validator
{
	const ADDRESS_REGEX = '/^[a-z0-9\/+]{32}$/i';
	const BLOCK_HASH_REGEX = '/^[a-f0-9]{64}$/';
	const HEIGHT_REGEX = '/^[0-9]{1,10}$/';

	public static function isAddress($address)
	{
		return ! ! preg_match(self::ADDRESS_REGEX, $address);
	}

	public static function isBlockHash($hash)
	{
		return ! ! preg_match(self::BLOCK_HASH_REGEX, $hash);
	}

	public static function isHeight($height)
	{
		return ! ! preg_match(self::HEIGHT_REGEX, $height);
	}
}
