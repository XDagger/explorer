<?php
namespace App\Http\Controllers\Api;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;

use App\Support\Api\Response;

class Controller extends BaseController
{
	use ValidatesRequests;

	/**
	 * Create ApiResponse instance
	 *
	 * @return Response
	 */
	public function response()
	{
		static $response;

		if (is_null($response)) {
			$response = resolve(Response::class);
		}

		return $response;
	}
}
