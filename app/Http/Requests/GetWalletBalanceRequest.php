<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

use App\Xdag\Block\Validation\Validator;

class GetWalletBalanceRequest extends FormRequest
{
	/**
	 * Determine if the user is authorized to make this request.
	 *
	 * @return bool
	 */
	public function authorize()
	{
		return true;
	}

	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array
	 */
	public function rules()
	{
		return [
			'address' => [
				'required',
				'regex:' . Validator::ADDRESS_REGEX,
				'not_in:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA',
			],
		];
	}
}
