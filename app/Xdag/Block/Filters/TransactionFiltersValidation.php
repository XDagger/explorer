<?php
namespace App\Xdag\Block\Filters;

use App\Xdag\Block\Validation\Validator;
use App\Xdag\Block\Filters\Base\FiltersValidator;

class TransactionFiltersValidation extends FiltersValidator
{
	public function getRules()
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
			$rules['transaction_directions.*'] = 'in:fee,input,output,earning';
		}

		return $rules;
	}
}
