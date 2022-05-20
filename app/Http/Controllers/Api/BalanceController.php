<?php namespace App\Http\Controllers\Api;

class BalanceController extends Controller
{
	public function getBalance($address)
	{
		if (strlen($address) < 32)
			$address = str_pad($address, 32, '/');

		try {
			return $this->response()->make([
				'balance' => $xdag->getBalance($address),
			]);
		} catch (\Exception $e) {
			return $this->response()->error('server_error', $e->getMessage(), 503);
		}
	}
}
