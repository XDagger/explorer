<?php
namespace App\Xdag\Block\Filters;

use Illuminate\Support\Carbon;
use App\Xdag\Block\Filters\Base\Filters;
use App\Xdag\Block\Line\LineParser;

class AddressFilters extends Filters
{
	protected $addressData;
	public $address = null, $dateFrom = null, $dateTo = null, $amountFrom = null, $amountTo = null, $remark = null;
	public $directions = [];

	public function forAddressData($line)
	{
		preg_match(LineParser::ADDRESS_REGEX, $line, $this->addressData);

		return $this;
	}

	public function passes()
	{
		$passes = true;
		if (! is_null($this->address) && ! $this->passesByAddressFilter())
			$passes = false;

		if (! is_null($this->dateFrom) && ! $this->passesByDateFromFilter())
			$passes = false;

		if (! is_null($this->dateTo) && ! $this->passesByDateToFilter())
			$passes = false;

		if (! is_null($this->amountFrom) && ! $this->passesByAmountFromFilter())
			$passes = false;

		if (! is_null($this->amountTo) && ! $this->passesByAmountToFilter())
			$passes = false;

		if (count($this->directions) > 0 && ! $this->passesByDirectionsFilter())
			$passes = false;

		if (! is_null($this->remark) && ! $this->passesByRemarkFilter())
			$passes = false;

		return $passes;
	}

	public function passesByAddressFilter()
	{
		$this->setUsedFilter('address');
		return str_contains($this->addressData[2], $this->address);
	}

	public function passesByDateFromFilter()
	{
		$this->setUsedFilter('date_from');

		return Carbon::parse($this->addressData[4])->gte(
			Carbon::parse($this->dateFrom)->setTime(0, 0, 0)
		);
	}

	public function passesByDateToFilter()
	{
		$this->setUsedFilter('date_to');

		return Carbon::parse($this->addressData[4])->lt(
			Carbon::parse($this->dateTo)->addDays(1)->setTime(0, 0, 0)
		);
	}

	public function passesByAmountFromFilter()
	{
		$this->setUsedFilter('amount_from');
		return (float) $this->addressData[3] >= (float) $this->amountFrom;
	}

	public function passesByAmountToFilter()
	{
		$this->setUsedFilter('amount_to');
		return (float) $this->addressData[3] <= (float) $this->amountTo;
	}

	public function passesByDirectionsFilter()
	{
		$this->setUsedFilter('directions');
		return in_array($this->addressData[1], $this->directions);
	}

	public function passesByRemarkFilter()
	{
		$this->setUsedFilter('remark');

		foreach (preg_split('/\s+/si', $this->remark) as $word) {
			if ($word === '')
				continue;

			if (stripos($this->addressData[5], $word) === false)
				return false;
		}

		return true;
	}
}
