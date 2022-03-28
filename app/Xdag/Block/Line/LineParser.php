<?php
namespace App\Xdag\Block\Line;

use Illuminate\Support\Str;

class LineParser
{
	const TRANSACTION_REGEX = '/^\s*(fee|input|output): ([a-zA-Z0-9\/+]{32})\s+([0-9]+\.[0-9]+)$/si';
	const ADDRESS_REGEX = '/^\s*(input|output|earning): ([a-zA-Z0-9\/+]{32})\s+([0-9]+\.[0-9]+)\s+([0-9]{4}-[0-9]{2}-[0-9]{2}\s+[0-9]{2}:[0-9]{2}:[0-9]{2}\.[0-9]{3})(.*)$/si';

	public function blockExists($line)
	{
		return stripos($line, 'Block is not found') === false && stripos($line, 'Illegal number') === false;
	}

	public function shouldProceedToTransactions($line)
	{
		return stripos($line, 'block as transaction: details') !== false;
	}

	public function shouldProcceedToAddresses($line)
	{
		return stripos($line, 'block as address: details') !== false;
	}

	public function parseProperty($line)
	{
		if (preg_match('/\s*(.*): (.*)/', $line, $matches)) {
			$key   = strtolower(trim($matches[1]));
			$value = trim($matches[2]);

			if ($key !== 'remark')
				$value = strtolower($value);

			$properties = [];

			if ($key == 'balance') {
				$properties['balance_address'] = trim(current($balance = explode(' ', $matches[2])));
				$value = end($balance);
			}

			if ($key == 'height')  {
				$value = ltrim($value, '0');
			}

			$properties[Str::snake($key)] = $value;

			return $properties;
		}

		return null;
	}

	public function isValidTransaction($line)
	{
		return ! ! preg_match(self::TRANSACTION_REGEX, $line);
	}

	public function parseTransaction($line)
	{
		if (preg_match(self::TRANSACTION_REGEX, $line, $matches)) {
			list(, $direction, $address, $amount) = $matches;

			return [
				'direction' => strtolower(trim($direction)),
				'address'	=> trim($address),
				'amount'	=> strtolower(trim($amount)),
			];
		}

		return null;
	}

	public function isValidAddress($line)
	{
		return ! ! preg_match(self::ADDRESS_REGEX, $line);
	}

	public function parseAddress($line)
	{
		if (preg_match(self::ADDRESS_REGEX, $line, $matches)) {
			list(, $direction, $address, $amount, $time, $remark) = $matches;

			return [
				'direction' => strtolower(trim($direction)),
				'address'	=> trim($address),
				'amount'	=> strtolower(trim($amount)),
				'time'		=> strtolower(trim($time)),
				'remark'	=> trim($remark),
			];
		}
	}
}
