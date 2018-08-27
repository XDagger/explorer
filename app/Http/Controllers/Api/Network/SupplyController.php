<?php
namespace App\Http\Controllers\Api\Network;

use App\Http\Controllers\Api\Controller;

use App\Modules\Network\Network;

class SupplyController extends Controller
{
	public function show()
	{
		$log = Network::latest()->first();

		return $this->response()->make([
			'supply' => $log ? $log->supply : 0,
		]);
	}

	public function raw()
	{
		$log = Network::latest()->first();

		return response($log ? $log->supply : 0, 200)->header('Content-Type', 'text/plain');
	}

	public function rawWithDecimals()
	{
		$log = Network::latest()->first();

		return response($log ? $log->supply . '.000000000' : '0.000000000', 200)->header('Content-Type', 'text/plain');
	}
}
