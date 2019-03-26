<?php
namespace App\Xdag\Block\Filters;

use App\Xdag\Block\Validation\Validator;
use App\Xdag\Block\Filters\Base\FiltersValidator;

class TransactionFiltersValidation extends FiltersValidator
{
	protected $is_api = false;

	public function __construct($is_api = false)
	{
		$this->is_api = $is_api;

		parent::__construct();
	}

	public function getRules()
	{
		if ($this->is_api) {
			return $this->apiFilterValidations();
		}

		return $this->webFilterValidations();
	}

	protected function apiFilterValidations()
	{
		$rules = [];

		if ($this->request->filled('transactions_address')) {
			$rules['transactions_address'] = [
				'regex:' . Validator::ADDRESS_REGEX,
				'not_in:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA',
			];
		}

		if ($this->request->filled('transactions_amount_from')) {
			$rules['transactions_amount_from'] = 'numeric|min:0';

			if ($this->request->filled('transactions_amount_to')) {
				$rules['transactions_amount_from'] .= '|lte:transactions_amount_to';
			}
		}

		if ($this->request->filled('transactions_amount_to')) {
			$rules['transactions_amount_to'] = 'numeric|min:0';

			if ($this->request->filled('transactions_amount_from')) {
				$rules['transactions_amount_to'] .= '|gte:transactions_amount_from';
			}
		}

		if ($this->request->filled('transactions_directions')) {
			$rules['transactions_directions']   = 'array';
			$rules['transactions_directions.*'] = 'in:fee,input,output';
		}

		return $rules;
	}

	protected function webFilterValidations()
	{
		$rules = [];

		if ($this->request->filled('transaction_address')) {
			$rules['transaction_address'] = [
				'regex:' . Validator::ADDRESS_REGEX,
				'not_in:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA',
			];
		}

		if ($this->request->filled('transaction_amount')) {
			$rules['transaction_amount'] = 'array';

			if ($this->request->filled('transaction_amount.from')) {
				$rules['transaction_amount.from'] = 'numeric|min:0';

				if ($this->request->filled('transaction_amount.to')) {
					$rules['transaction_amount.from'] .= '|lte:transaction_amount.to';
				}
			}

			if ($this->request->filled('transaction_amount.to')) {
				$rules['transaction_amount.to'] = 'numeric|min:0';

				if ($this->request->filled('transaction_amount.from')) {
					$rules['transaction_amount.to'] .= '|gte:transaction_amount.from';
				}
			}
		}

		if ($this->request->filled('transaction_directions')) {
			$rules['transaction_directions']   = 'array';
			$rules['transaction_directions.*'] = 'in:fee,input,output';
		}

		return $rules;
	}
}
