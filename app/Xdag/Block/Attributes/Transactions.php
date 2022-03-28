<?php
namespace App\Xdag\Block\Attributes;

class Transactions extends Movements
{
	public function fees()
	{
		return $this->itemsByDirections(['fee']);
	}

	public function inputs()
	{
		return $this->itemsByDirections(['input']);
	}

	public function outputs()
	{
		return $this->itemsByDirections(['output']);
	}

	public function getInputsSum()
	{
		return $this->itemsSumByDirections(['input']);
	}

	public function getOutputsSum()
	{
		return $this->itemsSumByDirections(['output']);
	}

	public function getFeesSum()
	{
		return $this->itemsSumByDirections(['fee']);
	}
}
