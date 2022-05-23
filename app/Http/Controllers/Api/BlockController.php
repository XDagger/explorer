<?php namespace App\Http\Controllers\Api;

use App\Xdag\Block\Cache;

class BlockController extends Controller
{
	public function index(string $id)
	{
		//
	}

	public function balance(string $id)
	{
		try {
			$balance = Cache::getBalance($id);
		} catch (\InvalidArgumentException $ex) {
			return response()->json(['error' => 'invalid_input', 'message' => $ex->getMessage()], 422);
		} catch (\Throwable $ex) {
			return response()->json(['error' => 'internal_error', 'message' => $ex->getMessage()], 500);
		}

		if (!$balance->blockExists())
			return response()->json(['error' => 'invalid_input', 'message' => 'Incorrect address, block hash or height.'], 422);

		return response()->json(['balance' => $balance->balance]);
	}
}
