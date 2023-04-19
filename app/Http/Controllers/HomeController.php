<?php namespace App\Http\Controllers;

use App\Xdag\Network\Stat;
use App\Xdag\Block\MainBlock;

class HomeController extends Controller
{
	public function index()
	{
		if (($stat = Stat::orderBy('id', 'desc')->limit(1)->first()) && ($previousStat = Stat::where('id', '<', $stat->id)->orderBy('id', 'desc')->limit(1)->first()))
			$numberOfNewBlocksLastMinute = max(0, $stat->blocks - $previousStat->blocks);
		else
			$numberOfNewBlocksLastMinute = 0;

		return view('home.index', [
			'stat' => $stat ?? new Stat,
			'hashrateChartData' => $this->hashrateChartData(),
			'newBlocksChartData' => $this->newBlocksChartData(),
			'hashrateChange' => valueChange((float) Stat::orderBy('id', 'desc')->offset(60)->limit(60)->get()->avg('network_hashrate'), (float) Stat::orderBy('id', 'desc')->limit(60)->get()->avg('network_hashrate')),
			'mainBlocks' => MainBlock::orderBy('height', 'desc')->limit(20)->get(),
			'numberOfNewBlocksLastMinute' => $numberOfNewBlocksLastMinute,
			'currentErrorMessage' => request()->has('400') ? 'Incorrect address, block hash or main block height.' : (request()->has('404') ? 'Block was not found. Please make sure you entered correct address, block hash or main block height.' : null),
		]);
	}

	protected function hashrateChartData(): array
	{
		$stats = Stat::selectRaw('AVG(network_hashrate) network_hashrate, DATE_FORMAT(created_at, "%Y-%m-%d %H:00:00") created_at')->groupByRaw('DATE_FORMAT(created_at, "%Y-%m-%d %H:00:00")')->orderByRaw('DATE_FORMAT(created_at, "%Y-%m-%d %H:00:00")')->get();

		$data = [
			'labels' => [],
			'values' => [],
		];

		foreach ($stats as $stat) {
			$data['labels'][] = $stat->created_at->format('m-d H:00');
			$data['values'][] = round($stat->network_hashrate / 1024 / 1024, 2); // Mh/s
		}

		return $data;
	}

	protected function newBlocksChartData(): array
	{
		$stats = Stat::orderBy('id', 'desc')->limit(61)->get()->toBase()->reverse()->values();

		$data = [
			'labels' => [],
			'values' => [],
		];

		if ($stats->count() < 2)
			return $data;

		$lastNumberOfBlocks = $stats->shift()->blocks;

		foreach ($stats as $stat) {
			$data['labels'][] = $stat->created_at->format('H:i');
			$data['values'][] = max(0, $stat->blocks - $lastNumberOfBlocks);

			$lastNumberOfBlocks = $stat->blocks;
		}

		return $data;
	}
}
