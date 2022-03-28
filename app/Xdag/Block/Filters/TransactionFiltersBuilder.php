<?php
namespace App\Xdag\Block\Filters;

use Illuminate\Support\Arr;

class TransactionFiltersBuilder
{
	public function fromArray(array $data)
	{
		$filters = new TransactionFilters;

		$amount = Arr::get($data, 'transaction_amount', []);

		$filters->address    = Arr::get($data, 'transaction_address');
		$filters->directions = Arr::get($data, 'transaction_directions', []);
		$filters->amountFrom = Arr::get($amount, 'from');
		$filters->amountTo   = Arr::get($amount, 'to');

		return $filters;
	}

	public function fromApi(array $data)
	{
		$filters = new TransactionFilters;

		$filters->address    = Arr::get($data, 'transactions_address');
		$filters->directions = Arr::get($data, 'transactions_directions', []);
		$filters->amountFrom = Arr::get($data, 'transactions_amount_from');
		$filters->amountTo   = Arr::get($data, 'transactions_amount_to');

		return $filters;
	}
}
