<?php
namespace App\Xdag\Block\Filters;

use App\Xdag\Block\Validation\Validator;
use App\Xdag\Block\Filters\Base\FiltersValidator;

class AddressFiltersValidation extends FiltersValidator
{
	public function getRules()
	{
		$rules = [];

		if ($this->request->filled('address')) {
			$rules['address'] = [
				'regex:' . Validator::ADDRESS_REGEX,
				'not_in:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA',
			];
		}

		if ($this->request->filled('date')) {
			$rules['date'] = 'array';

			if ($this->request->filled('date.from')) {
				$rules['date.from'] = 'date_format:Y-m-d';

				if ($this->request->filled('date.to')) {
					$rules['date.from'] .= '|before_or_equal:date.to';
				}
			}

			if ($this->request->filled('date.to')) {
				$rules['date.to'] = 'date_format:Y-m-d';

				if ($this->request->filled('date.from')) {
					$rules['date.to'] .= '|after_or_equal:date.from';
				}
			}
		}

		if ($this->request->filled('amount')) {
			$rules['amount'] = 'array';

			if ($this->request->filled('amount.from')) {
				$rules['amount.from'] = 'numeric|min:0';

				if ($this->request->filled('amount.to')) {
					$rules['amount.from'] .= '|lte:amount.to';
				}
			}

			if ($this->request->filled('amount.to')) {
				$rules['amount.to'] = 'numeric|min:0';

				if ($this->request->filled('amount.from')) {
					$rules['amount.to'] .= '|gte:amount.from';
				}
			}
		}

		if ($this->request->filled('directions')) {
			$rules['directions']   = 'array';
			$rules['directions.*'] = 'in:fee,input,output,earning';
		}

		return $rules;
	}
}
