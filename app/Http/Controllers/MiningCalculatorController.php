<?php
namespace App\Http\Controllers;

class MiningCalculatorController extends Controller
{
	const MAX_BLOCKS_PER_DAY_ON_NETWORK = (3600 * 24) / 64;

	public function index()
	{
		$log = Network::orderBy('id', 'desc')->limit(1)->first();
		$hashrate = $log ? $log->hashrate : 0;
		$reward = Block::getReward();

		return view('mining-calculator.index', compact('hashrate', 'reward'));
	}
}
