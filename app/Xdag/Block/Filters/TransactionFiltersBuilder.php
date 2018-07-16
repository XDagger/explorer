<?php
namespace App\Xdag\Block\Filters;

class TransactionFiltersBuilder
{
	public function fromArray(array $data)
	{
		$filters = new TransactionFilters;

		$amount = array_get($data, 'transaction_amount', []);

		$filters->address	 = array_get($data, 'transaction_address');
		$filters->directions = array_get($data, 'transaction_directions', []);
		$filters->amountFrom = array_get($amount, 'from');
		$filters->amountTo	 = array_get($amount, 'to');

		return $filters;
	}
}
