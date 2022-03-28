<?php
namespace App\Xdag\Block\Attributes;

use Illuminate\Support\Collection;

class Addresses extends Movements
{
	public function getEarningsSum()
	{
		return $this->itemsSumByDirections(['input', 'earning']);
	}

	public function getSpendingsSum()
	{
		return $this->itemsSumByDirections(['output']);
	}
}
