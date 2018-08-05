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
}
