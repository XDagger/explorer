<?php namespace App\Http\Controllers;

use App\Xdag\Network\Stat;
use App\Xdag\Block\MainBlock;

class HomeController extends Controller
{
	public function index()
	{
		if (($stat = Stat::orderBy('id', 'desc')->limit(1)->first()) && ($previousStat = Stat::where('id', '<', $stat->id)->orderBy('id', 'desc')->limit(1)->first()))
			$numberOfNewBlocksLastMinute = $stat->blocks - $previousStat->blocks;
		else
			$numberOfNewBlocksLastMinute = 0;

		return view('home.index', [
			'stat' => $stat ?? new Stat,
			'hashrateChartData' => $this->hashrateChartData(),
			'newBlocksChartData' => $this->newBlocksChartData(),
			'hashrateChange' => valueChange((float) Stat::orderBy('id', 'desc')->offset(60)->limit(60)->avg('hashrate'), (float) Stat::orderBy('id', 'desc')->limit(60)->avg('hashrate')),
			'mainBlocks' => MainBlock::orderBy('height', 'desc')->get(),
			'numberOfNewBlocksLastMinute' => $numberOfNewBlocksLastMinute,
		]);
	}

	protected function hashrateChartData(): array
	{
		$stats = Stat::selectRaw('AVG(hashrate) hashrate, DATE_FORMAT(created_at, "%Y-%m-%d %H:00:00") created_at')->groupBy('created_at')->orderBy('created_at')->get();

		$data = [
			'labels' => [],
			'values' => [],
		];

		foreach ($stats as $stat) {
			$data['labels'][] = $stat->created_at->format('m-d H:00');
			$data['values'][] = round($stat->hashrate / 1024 / 1024, 2); // Mh/s
		}

		return $data;
	}

	protected function newBlocksChartData(): array
	{
		$stats = Stat::orderBy('id', 'desc')->limit(61)->get();

		$data = [
			'labels' => [],
			'values' => [],
		];

		if ($stats->count() < 2)
			return $data;

		$lastNumberOfBlocks = $stats->shift()->blocks;

		foreach ($stats as $stat) {
			$data['labels'][] = $stat->created_at->format('H:i');
			$data['values'][] = $stat->blocks - $lastNumberOfBlocks;

			$lastNumberOfBlocks = $stat->blocks;
		}

		return $data;
	}
}
