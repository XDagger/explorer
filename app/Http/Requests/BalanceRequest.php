<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BalanceRequest extends FormRequest
{
	public function authorize()
	{
		return true;
	}

	public function rules()
	{
		return [
			'input' => [
				'required',
				'regex:/^([a-zA-Z0-9\/+]{32}|[a-f0-9]{64})$/',
				'not_in:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA',
			],
		];
	}
}
