<?php namespace App\Xdag\Block\Listing;

use Illuminate\Support\Facades\DB;

class TransactionsListing extends Listing
{
	protected $pagination = ['transactions_page', 'transactions_per_page'];

	public function inputsSum(): string
	{
		return $this->buildQuery()->whereDirection('input')->sum('amount');
	}

	public function outputsSum(): string
	{
		return $this->buildQuery()->whereDirection('output')->sum(DB::raw('ABS(amount)'));
	}

	public function feesSum(): string
	{
		return $this->buildQuery()->whereDirection('fee')->sum(DB::raw('ABS(amount)'));
	}

	protected function builder()
	{
		return $this->block->transactions()->transaction();
	}

	protected function filters(): array
	{
		return  [
			'transactions_address' => [
				'name' => 'Address',
				'validation' => 'nullable|regex:/^[1-9A-HJ-NP-Za-km-z]{26,33}$/u', // XDAG-ADDRESS related code
				'apply' => fn($builder, string $value) => $builder->whereAddress($value),
			],

			'transactions_amount_from' => [
				'name' => 'Amount from',
				'validation' => 'nullable|numeric|min:0' . ($this->request->input('transactions_amount_to') !== null ? '|lte:transactions_amount_to' : ''),
				'apply' => fn($builder, string $value) => $builder->where(DB::raw('ABS(amount)'), '>=', $value),
			],

			'transactions_amount_to' => [
				'name' => 'Amount to',
				'validation' => 'nullable|numeric|min:0' . ($this->request->input('transactions_amount_from') !== null ? '|gte:transactions_amount_from' : ''),
				'apply' => fn($builder, string $value) => $builder->where(DB::raw('ABS(amount)'), '<=', $value),
			],

			'transactions_directions' => [
				'name' => 'Directions',
				'validation' => [
					'transactions_directions' => 'nullable|array',
					'transactions_directions.*' => 'in:input,output,fee',
				],
				'apply' => fn($builder, array $value) => $builder->whereIn('direction', $value),
			],
		];
	}
}
