<?php
namespace App\Xdag\Block\Filters\Base;

abstract class FiltersValidator
{
	protected $request, $validator;

	public function __construct()
	{
		$this->request	 = request();
		$this->validator = validator($this->request->toArray(), $this->getRules());
	}

	abstract public function getRules();

	public function data()
	{
		if ($this->validator->fails()) {
			return $this->request->except(
				$this->validator->errors()->keys()
			);
		}

		return $this->request->toArray();
	}

	public function errors()
	{
		return $this->validator->errors();
	}

	public function firstError($field)
	{
		return str_replace('.', ' ', $this->errors()->first($field));
	}
}
