<?php
namespace App\Xdag\Block\Filters;

class TransactionFiltersBuilder
{
	public function fromArray(array $data)
	{
		$filters = new TransactionFilters;

		$amount = array_get($data, 'transaction_amount', []);

		$filters->address    = array_get($data, 'transaction_address');
		$filters->directions = array_get($data, 'transaction_directions', []);
		$filters->amountFrom = array_get($amount, 'from');
		$filters->amountTo   = array_get($amount, 'to');

		return $filters;
	}

	public function fromApi(array $data)
	{
		$filters = new TransactionFilters;

		$filters->address    = array_get($data, 'transactions_address');
		$filters->directions = array_get($data, 'transactions_directions', []);
		$filters->amountFrom = array_get($data, 'transactions_amount_from');
		$filters->amountTo   = array_get($data, 'transactions_amount_to');

		return $filters;
	}
}
