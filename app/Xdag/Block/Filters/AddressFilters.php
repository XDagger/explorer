<?php
namespace App\Xdag\Block\Filters;

use Illuminate\Support\Carbon;
use App\Xdag\Block\Filters\Base\Filters;
use App\Xdag\Block\Line\LineParser;

class AddressFilters extends Filters
{
	protected $addressData;
	public $address = null, $dateFrom = null, $dateTo = null, $amountFrom = null, $amountTo = null;
	public $directions = [];

	public function forAddressData($line)
	{
		preg_match(LineParser::ADDRESS_REGEX, $line, $this->addressData);

		return $this;
	}

	public function passes()
	{
		$passes = true;

		if (! is_null($this->address) && ! $this->passesByAddressFilter()) {
			$passes = false;
		}

		if (! is_null($this->dateFrom) && ! $this->passesByDateFromFilter()) {
			$passes = false;
		}

		if (! is_null($this->dateTo) && ! $this->passesByDateToFilter()) {
			$passes = false;
		}

		if (! is_null($this->amountFrom) && ! $this->passesByAmountFromFilter()) {
			$passes = false;
		}

		if (! is_null($this->amountTo) && ! $this->passesByAmountToFilter()) {
			$passes = false;
		}

		if (count($this->directions) > 0 && ! $this->passesByDirectionsFilter()) {
			$passes = false;
		}

		return $passes;
	}

	public function passesByAddressFilter()
	{
		$passes = str_contains($this->addressData[2], $this->address);

		$this->setUsedFilter('address');

		return $passes;
	}

	public function passesByDateFromFilter()
	{
		$passes = Carbon::parse($this->addressData[4])->gte(
			Carbon::parse($this->dateFrom)->setTime(0, 0, 0)
		);

		$this->setUsedFilter('date_from');

		return $passes;
	}

	public function passesByDateToFilter()
	{
		$passes = Carbon::parse($this->addressData[4])->lt(
			Carbon::parse($this->dateTo)->addDays(1)->setTime(0, 0, 0)
		);

		$this->setUsedFilter('date_to');

		return $passes;
	}

	public function passesByAmountFromFilter()
	{
		$passes = (float)$this->addressData[3] >= (float)$this->amountFrom;

		$this->setUsedFilter('amount_from');

		return $passes;
	}

	public function passesByAmountToFilter()
	{
		$passes = (float)$this->addressData[3] <= (float)$this->amountTo;

		$this->setUsedFilter('amount_to');

		return $passes;
	}

	public function passesByDirectionsFilter()
	{
		$passes = in_array($this->addressData[1], $this->directions);

		$this->setUsedFilter('directions');

		return $passes;
	}
}
