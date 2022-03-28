<?php
namespace App\Xdag\Block\Filters;

use Illuminate\Support\Arr;

class AddressFiltersBuilder
{
	public function fromArray(array $data)
	{
		$filters = new AddressFilters;

		$amount = Arr::get($data, 'amount', []);
		$date	= Arr::get($data, 'date', []);

		$filters->address    = Arr::get($data, 'address');
		$filters->directions = Arr::get($data, 'directions', []);
		$filters->amountFrom = Arr::get($amount, 'from');
		$filters->amountTo   = Arr::get($amount, 'to');
		$filters->dateFrom   = Arr::get($date, 'from');
		$filters->dateTo     = Arr::get($date, 'to');
		$filters->remark     = Arr::get($data, 'remark');

		return $filters;
	}

	public function fromApi(array $data)
	{
		$filters = new AddressFilters;

		$filters->address    = Arr::get($data, 'addresses_address');
		$filters->directions = Arr::get($data, 'addresses_directions', []);
		$filters->amountFrom = Arr::get($data, 'addresses_amount_from');
		$filters->amountTo   = Arr::get($data, 'addresses_amount_to');
		$filters->dateFrom   = Arr::get($data, 'addresses_date_from');
		$filters->dateTo     = Arr::get($data, 'addresses_date_to');
		$filters->remark     = Arr::get($data, 'addresses_remark');

		return $filters;
	}
}
