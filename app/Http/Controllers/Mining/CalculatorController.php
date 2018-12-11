<?php
namespace App\Http\Controllers\Mining;

use App\Http\Controllers\Controller;

use App\Xdag\Hashpower;
use App\Modules\Network\Network;

class CalculatorController extends Controller
{
	const MAX_COINS_PER_DAY_ON_NETWORK = (3600 * 24) / 64 * 1024;

	public function index()
	{
		$log = Network::orderBy('id', 'desc')->limit(1)->first();
		$hashrate = $log ? $log->hashrate : 0;

		return view($this->resolveView('mining-calculator.index', 'mining-calculator.text-index'), compact('hashrate'));
	}

	public function calculate()
	{
		$log = Network::orderBy('id', 'desc')->limit(1)->first();
		$networkHashrate = $log ? $log->hashrate : 0;

		$hashrateInHs = ((float) request('hashrate')) * Hashpower::GHS;

		$networkHashrate += $hashrateInHs;

		$result = number_format(round($hashrateInHs * self::MAX_COINS_PER_DAY_ON_NETWORK / $networkHashrate, 9), 9);

		return view('mining-calculator.text-index', compact('result'));
	}
}
