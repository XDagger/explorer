<?php
namespace App\Xdag;

class Hashpower
{
	const GHS = 1024 * 1024 * 1024;
	const THS = 1024 * 1024 * 1024 * 1024;

	/**
	 * Format float value into human readable hashpower
	 *
	 * @param float $value
	 * @param int	$precision
	 *
	 * @return string
	 */
	public static function format($value, $precision = 9)
	{
		$units = ['h/s', 'Kh/s', 'Mh/s', 'Gh/s', 'Th/s', 'Ph/s', 'Eh/s', 'Zh/s', 'Yh/s'];
		$unit  = intval(log(abs(intval($value)), 1024));

		if (array_key_exists($unit, $units)) {
			return round($value / pow(1024, $unit), $precision) . ' ' . $units[$unit];
		}

		return round($value, $precision);
	}
}
