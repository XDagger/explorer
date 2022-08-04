<?php namespace App\Http\Controllers;

use App\Xdag\Network\Stat;

class MiningCalculatorController extends Controller
{
	public function index()
	{
		$stat = Stat::orderBy('id', 'desc')->limit(1)->first();

		return view('mining-calculator.index', [
			'network_hashrate' => $stat->network_hashrate ?? 0,
			'block_reward' => $stat->block_reward ?? 0,
		]);
	}
}
