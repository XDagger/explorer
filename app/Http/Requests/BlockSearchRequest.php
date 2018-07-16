<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

use App\Xdag\Block\Validation\Validator;

class BlockSearchRequest extends FormRequest
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
			'search_address_or_hash' => [
				'required',
				'regex:/^([a-zA-Z0-9\/+]{32}|[a-f0-9]{64})$/',
				'not_in:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA',
			],
		];
	}
}
