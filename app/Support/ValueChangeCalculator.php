<?php
namespace App\Support;

class ValueChangeCalculator
{
	/**
	 * @param $currentValue
	 *
	 * @return array
	 */
	public function calculate($previousValue, $currentValue)
	{
		$difference	 = $currentValue - $previousValue;
		$valueChange = abs($difference);
		$increased	 = $difference > 0;

		if ($previousValue == 0 && $currentValue == 0) {
			$percentageChange = 0.0;
		} else if ($previousValue == 0) {
			$percentageChange = 100;
		} else {
			$percentageChange = round(($difference / $previousValue) * 100, 2);
		}

		$isSame = $percentageChange === 0.0;

		return compact('increased', 'percentageChange', 'isSame', 'valueChange', 'currentValue', 'previousValue');
	}
}
