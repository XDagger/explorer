<?php
namespace App\Http\Controllers\Api\Wallet;

use Symfony\Component\HttpFoundation\Response;

use App\Http\Controllers\Api\Controller;

use App\Xdag\XdagInterface;
use App\Xdag\Block\Validation\Validator;

class BalanceCheckerController extends Controller
{
	public function getBalance($address, XdagInterface $xdag)
	{
		if (strlen($address) < 32)
			$address = str_pad($address, 32, '/');

		if (! Validator::isAddress($address)) {
			return $this->response()->error('invalid_input', 'Incorrect address.', Response::HTTP_UNPROCESSABLE_ENTITY);
		}

		try {
			return $this->response()->make([
				'balance' => $xdag->getBalance($address),
			]);
		} catch (\Exception $e) {
			return $this->response()->error('server_error', $e->getMessage(), Response::HTTP_SERVICE_UNAVAILABLE);
		}
	}
}
