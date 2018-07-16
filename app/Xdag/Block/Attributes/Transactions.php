<?php
namespace App\Xdag\Block\Attributes;

use Illuminate\Support\Collection;

class Transactions extends Collection
{
	public function getTotalFee()
	{
		$fees = 0;
		bcscale(9);

		foreach ($this->all() as $item) {
			if ($item['direction'] == 'fee') {
				$fees = bcadd($fees, $item['amount']);
			}
		}

		return $fees;
	}

	public function inputs()
	{
		$inputs = [];

		foreach ($this->all() as $item) {
			if ($item['direction'] == 'input') {
				$inputs[] = $item;
			}
		}

		return collect($inputs);
	}

	public function outputs()
	{
		$outputs = [];

		foreach ($this->all() as $item) {
			if ($item['direction'] == 'output') {
				$outputs[] = $item;
			}
		}

		return collect($outputs);
	}

	public function getInputsSum()
	{
		$sum = 0;
		bcscale(9);

		foreach ($this->all() as $item) {
			if ($item['direction'] == 'input') {
				$sum = bcadd($sum, $item['amount']);
			}
		}

		return $sum;
	}

	public function getOutputsSum()
	{
		$sum = 0;
		bcscale(9);

		foreach ($this->all() as $item) {
			if ($item['direction'] == 'output') {
				$sum = bcadd($sum, $item['amount']);
			}
		}

		return $sum;
	}
}
