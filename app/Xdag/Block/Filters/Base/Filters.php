<?php
namespace App\Xdag\Block\Filters\Base;

abstract class Filters
{
	protected $usedFilters = [];

	protected function setUsedFilter($filterName)
	{
		$this->usedFilters[] = $filterName;

		return $this;
	}

	public function hasUsedFilter($filterName)
	{
		return in_array($filterName, $this->usedFilters);
	}

	public function isUsed()
	{
		return count($this->usedFilters) > 0;
	}

	abstract public function passes();
}
