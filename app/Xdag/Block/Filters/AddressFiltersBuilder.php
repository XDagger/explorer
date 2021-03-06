<?php
namespace App\Xdag\Block\Filters;

class AddressFiltersBuilder
{
	public function fromArray(array $data)
	{
		$filters = new AddressFilters;

		$amount = array_get($data, 'amount', []);
		$date	= array_get($data, 'date', []);

		$filters->address    = array_get($data, 'address');
		$filters->directions = array_get($data, 'directions', []);
		$filters->amountFrom = array_get($amount, 'from');
		$filters->amountTo   = array_get($amount, 'to');
		$filters->dateFrom   = array_get($date, 'from');
		$filters->dateTo     = array_get($date, 'to');
		$filters->remark     = array_get($data, 'remark');

		return $filters;
	}

	public function fromApi(array $data)
	{
		$filters = new AddressFilters;

		$filters->address    = array_get($data, 'addresses_address');
		$filters->directions = array_get($data, 'addresses_directions', []);
		$filters->amountFrom = array_get($data, 'addresses_amount_from');
		$filters->amountTo   = array_get($data, 'addresses_amount_to');
		$filters->dateFrom   = array_get($data, 'addresses_date_from');
		$filters->dateTo     = array_get($data, 'addresses_date_to');
		$filters->remark     = array_get($data, 'addresses_remark');

		return $filters;
	}
}
