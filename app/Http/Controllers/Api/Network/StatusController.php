<?php
namespace App\Http\Controllers\Api\Network;

use App\Http\Controllers\Api\Controller;

class StatusController extends Controller
{
	public function show()
	{
		return $this->response()->make(json_decode(file_get_contents(storage_path('cache/status.json')), true));
	}
}
