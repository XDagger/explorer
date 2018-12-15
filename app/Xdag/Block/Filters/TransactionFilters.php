<?php
namespace App\Xdag\Block\Filters;

use App\Xdag\Block\Filters\Base\Filters;
use App\Xdag\Block\Line\LineParser;

class TransactionFilters extends Filters
{
	protected $transactionData;
	public $address = null, $amountFrom = null, $amountTo = null;
	public $directions = [];

	public function forTransactionData($line)
	{
		preg_match(LineParser::TRANSACTION_REGEX, $line, $this->transactionData);

		return $this;
	}

	public function passes()
	{
		if (! is_null($this->address) && ! $this->passesByAddressFilter())
			return false;

		if (! is_null($this->amountFrom) && ! $this->passesByAmountFromFilter())
			return false;

		if (! is_null($this->amountTo) && ! $this->passesByAmountToFilter())
			return false;

		if (count($this->directions) > 0 && ! $this->passesByDirectionsFilter())
			return false;

		return true;
	}

	public function passesByAddressFilter()
	{
		$this->setUsedFilter('address');
		return str_contains($this->transactionData[2], $this->address);
	}

	public function passesByAmountFromFilter()
	{
		$this->setUsedFilter('amount_from');
		return (float) $this->transactionData[3] >= (float) $this->amountFrom;
	}

	public function passesByAmountToFilter()
	{
		$this->setUsedFilter('amount_to');
		return (float) $this->transactionData[3] <= (float) $this->amountTo;
	}

	public function passesByDirectionsFilter()
	{
		$this->setUsedFilter('directions');
		return in_array($this->transactionData[1], $this->directions);
	}
}
