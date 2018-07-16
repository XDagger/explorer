<?php
namespace App\Xdag\Block\Validation;

class Validator
{
	const ADDRESS_REGEX = '/^[a-z0-9\/+]{32}$/i';
	const BLOCK_HASH_REGEX = '/^[a-f0-9]{64}$/';

	public static function isAddress($address)
	{
		return ! ! preg_match(self::ADDRESS_REGEX, $address);
	}

	public static function isBlockHash($hash)
	{
		return ! ! preg_match(self::BLOCK_HASH_REGEX, $hash);
	}
}
