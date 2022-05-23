<?php namespace App\Http\Controllers;

use App\Xdag\Network\Stat;
use App\Xdag\Block\MainBlock;

class MiningCalculatorController extends Controller
{
	public function index()
	{
		return view('mining-calculator.index', [
			'hashrate' => Stat::orderBy('id', 'desc')->limit(1)->first()->hashrate ?? 0,
			'reward' => MainBlock::orderBy('height', 'desc')->limit(1)->first()->balance ?? 0,
		]);
	}
}
