<?php namespace App\Xdag\Block\Listing;

use App\Xdag\Block\Block;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB, Validator};
use Illuminate\Pagination\AbstractPaginator;

abstract class Listing
{
	protected $block, $request;
	protected $pagination = ['page', 'per_page'];
	protected $usedFilters = [], $errors = [];

	public function __construct(Block $block, Request $request)
	{
		$this->block = $block;
		$this->request = $request;
		$this->buildFilters();
	}

	public function isFiltered(): bool
	{
		return count($this->usedFilters) > 0;
	}

	public function usedFilters(): array
	{
		return $this->usedFilters;
	}

	public function errors(): array
	{
		return $this->errors;
	}

	public function get(): AbstractPaginator
	{
		$page = max(1, (int) $this->request->input($this->pagination[0], 1));
		$perPage = max(1, (int) $this->request->input($this->pagination[1], 10000000000));

		return $this->buildQuery()->paginate($perPage, ['*'] /* columns */, $this->pagination[0] /* page name */, $page)->withQueryString();
	}

	public function earningsSum(): string
	{
		return $this->buildQuery()->earnings()->sum('amount');
	}

	public function spendingsSum(): string
	{
		return $this->buildQuery()->spendings()->sum(DB::raw('ABS("amount")'));
	}

	protected function buildQuery()
	{
		$builder = $this->builder();

		foreach ($this->usedFilters as $filter)
			call_user_func($filter['apply'], $builder, $filter['value']);

		return $builder;
	}

	protected function buildFilters(): void
	{
		foreach ($this->filters() as $key => $filter) {
			$validator = Validator::make($this->request->all(), is_array($filter['validation']) ? $filter['validation'] : [$key => $filter['validation']]);

			if ($validator->fails()) {
				$this->errors[$key] = $validator->errors()->all()[0];
				continue;
			}

			if ($this->request->input($key) === null)
				continue;

			$this->usedFilters[$key] = $filter;
			$this->usedFilters[$key]['value'] = $this->request->input($key);
		}
	}

	abstract protected function builder();
	abstract protected function filters(): array;
}