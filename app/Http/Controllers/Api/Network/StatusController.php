<?php
namespace App\Http\Controllers\Api\Network;

use App\Http\Controllers\Api\Controller;

use App\Xdag\XdagInterface;

class StatusController extends Controller
{
	/**
	 * @var \App\Xdag\XdagInterface
	 */
	protected $xdag;

	/**
	 * StatusController constructor.
	 *
	 * @param \App\Xdag\XdagInterface $xdag
	 */
	public function __construct(XdagInterface $xdag)
	{
		$this->xdag = $xdag;
	}

	/**
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function show()
	{
		return $this->response()->make(json_decode(file_get_contents(storage_path('cache/status.json')), true));
	}
}
