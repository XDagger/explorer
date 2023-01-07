<?php namespace App\Xdag\Block\Listing;

class WalletListing extends Listing
{
	protected $pagination = ['addresses_page', 'addresses_per_page'];

	protected function builder()
	{
		return $this->block->transactions()->wallet();
	}

	protected function filters(): array
	{
		return  [
			'addresses_address' => [
				'name' => 'Address',
				'validation' => 'nullable|regex:/^[a-zA-Z0-9\/+]{32,33}$/u',
				'apply' => fn($builder, string $value) => $builder->whereAddress($value),
			],

			'addresses_date_from' => [
				'name' => 'Date from',
				'validation' => 'nullable|date_format:Y-m-d|before_or_equal:addresses_date_to',
				'apply' => fn($builder, string $value) => $builder->where('created_at', '>=', "$value 00:00:00.000"),
			],

			'addresses_date_to' => [
				'name' => 'Date to',
				'validation' => 'nullable|date_format:Y-m-d|after_or_equal:addresses_date_from',
				'apply' => fn($builder, string $value) => $builder->where('created_at', '<=', "$value 23:59:59.999"),
			],

			'addresses_amount_from' => [
				'name' => 'Amount from',
				'validation' => 'nullable|numeric|min:0' . ($this->request->input('addresses_amount_to') !== null ? '|lte:addresses_amount_to' : ''),
				'apply' => fn($builder, string $value) => $builder->where('amount', '>=', $value),
			],

			'addresses_amount_to' => [
				'name' => 'Amount to',
				'validation' => 'nullable|numeric' . ($this->request->input('addresses_amount_from') !== null ? '|gte:addresses_amount_from' : ''),
				'apply' => fn($builder, string $value) => $builder->where('amount', '<=', $value),
			],

			'addresses_directions' => [
				'name' => 'Directions',
				'validation' => [
					'addresses_directions' => 'nullable|array',
					'addresses_directions.*' => 'in:input,output,earning,snapshot',
				],
				'apply' => fn($builder, array $value) => $builder->whereIn('direction', $value),
			],

			'addresses_remark' => [
				'name' => 'Remark',
				'validation' => 'nullable',
				'apply' => fn($builder, string $value) => $builder->where('remark', 'like', '%' . escapeLike($value) . '%'),
			],
		];
	}
}
