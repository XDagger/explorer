<?php
namespace App\Xdag\Block\Attributes;

use Illuminate\Support\Collection;

class Movements extends Collection
{
	protected function itemsByDirections(array $directions)
	{
		$items = [];

		foreach ($this->all() as $item) {
			if (in_array($item['direction'], $directions)) {
				$items[] = $item;
			}
		}

		return collect($items);
	}

	protected function itemsSumByDirections(array $directions)
	{
		$sum = 0;
		bcscale(9);

		foreach ($this->itemsByDirections($directions) as $item)
			$sum = bcadd($sum, $item['amount']);

		return $sum;
	}
}
