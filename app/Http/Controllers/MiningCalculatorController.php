<?php namespace App\Http\Controllers;

use App\Xdag\Network\Stat;

class MiningCalculatorController extends Controller
{
	public function index()
	{
		$stat = Stat::orderBy('id', 'desc')->limit(1)->first();

		return view('mining-calculator.index', [
			'hashrate' => $stat->network_hashrate ?? 0,
			'reward' => $stat->block_reward ?? 0,
		]);
	}
}
