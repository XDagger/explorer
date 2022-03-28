<?php
namespace App\Xdag\Block\Filters;

use App\Xdag\Block\Validation\Validator;
use App\Xdag\Block\Filters\Base\FiltersValidator;

class AddressFiltersValidation extends FiltersValidator
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

		if ($this->request->filled('addresses_address')) {
			$rules['addresses_address'] = [
				'regex:' . Validator::ADDRESS_REGEX,
				'not_in:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA',
			];
		}

		if ($this->request->filled('addresses_date_from')) {
			$rules['addresses_date_from'] = 'date_format:Y-m-d';

			if ($this->request->filled('addresses_date_to')) {
				$rules['addresses_date_from'] .= '|before_or_equal:addresses_date_to';
			}
		}

		if ($this->request->filled('addresses_date_to')) {
			$rules['addresses_date_to'] = 'date_format:Y-m-d';

			if ($this->request->filled('addresses_date_from')) {
				$rules['addresses_date_to'] .= '|after_or_equal:addresses_date_from';
			}
		}

		if ($this->request->filled('addresses_amount_from')) {
			$rules['addresses_amount_from'] = 'numeric|min:0';

			if ($this->request->filled('addresses_amount_to')) {
				$rules['addresses_amount_from'] .= '|lte:addresses_amount_to';
			}
		}

		if ($this->request->filled('addresses_amount_to')) {
			$rules['addresses_amount_to'] = 'numeric|min:0';

			if ($this->request->filled('addresses_amount_from')) {
				$rules['addresses_amount_to'] .= '|gte:addresses_amount_from';
			}
		}

		if ($this->request->filled('addresses_directions')) {
			$rules['addresses_directions']   = 'array';
			$rules['addresses_directions.*'] = 'in:input,output,earning';
		}

		if ($this->request->filled('addresses_remark')) {
			$rules['addresses_remark'] = 'string';
		}

		return $rules;
	}

	protected function webFilterValidations()
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
			$rules['directions.*'] = 'in:input,output,earning';
		}

		if ($this->request->filled('remark')) {
			$rules['remark'] = 'string';
		}

		return $rules;
	}
}
