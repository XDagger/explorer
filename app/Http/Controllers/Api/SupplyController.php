<?php namespace App\Http\Controllers\Api;

use App\Xdag\Network\Stat;

class SupplyController extends Controller
{
	const TOTAL_SUPPLY = 1446294144.000000000;

	public function index()
	{
		return response()->json([
			'supply' => (int) $this->getSupply(),
		]);
	}

	public function raw()
	{
		return response((int) $this->getSupply(), 200)->header('Content-Type', 'text/plain');
	}

	public function coinGeckoWithSeparators()
	{
		return response(number_format($this->getSupply(), 9, '.', ','), 200)->header('Content-Type', 'text/plain');
	}

	public function coinGeckoWithoutSeparators()
	{
		return response(number_format($this->getSupply(), 9, '', ''), 200)->header('Content-Type', 'text/plain');
	}

	public function indexTotal()
	{
		return response()->json([
			'total_supply' => (int) self::TOTAL_SUPPLY,
		]);
	}

	public function rawTotal()
	{
		return response((int) self::TOTAL_SUPPLY, 200)->header('Content-Type', 'text/plain');
	}

	public function coinGeckoWithSeparatorsTotal()
	{
		return response(number_format(self::TOTAL_SUPPLY, 9, '.', ','), 200)->header('Content-Type', 'text/plain');
	}

	public function coinGeckoWithoutSeparatorsTotal()
	{
		return response(number_format(self::TOTAL_SUPPLY, 9, '', ''), 200)->header('Content-Type', 'text/plain');
	}

	protected function getSupply()
	{
		return Stat::orderBy('id', 'desc')->limit(1)->first()->supply ?? 0;
	}
}
