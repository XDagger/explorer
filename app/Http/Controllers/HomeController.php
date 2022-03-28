<?php
namespace App\Http\Controllers;

use App\Xdag\XdagInterface;

use App\Support\ValueChangeCalculator;

use App\Modules\Network\Network;
use App\Modules\LastBlock\LastBlock;
use App\Modules\Network\Charts\HashrateLastDaysChart;
use App\Modules\Network\Charts\BlocksLastHourChart;

class HomeController extends Controller
{
	public function index(XdagInterface $xdag, ValueChangeCalculator $change)
	{
		$hashrate_chart = resolve(HashrateLastDaysChart::class);
		$hashrate_chart = [
			'days' => $hashrate_chart->keys()->toJson(JSON_HEX_APOS),
			'data' => $hashrate_chart->values()->toJson(JSON_HEX_APOS),
		];

		$blocks_chart = resolve(BlocksLastHourChart::class);
		$blocks_chart = [
			'hours' => $blocks_chart->keys()->toJson(JSON_HEX_APOS),
			'data' => $blocks_chart->values()->toJson(JSON_HEX_APOS),
		];

		$hashrate_change = $change->calculate(
			(float) Network::orderBy('id', 'desc')->offset(60)->limit(60)->get()->map->hashrate->avg(),
			(float) Network::orderBy('id', 'desc')->offset(0)->limit(60)->get()->map->hashrate->avg()
		);

		$new_blocks = 0;

		if ($last_log = Network::orderBy('id', 'desc')->limit(1)->first()) {
			$previous_log = Network::where('id', '<', $last_log->id)->orderBy('id', 'desc')->limit(1)->first();

			if ($previous_log)
				$new_blocks = $last_log->blocks - $previous_log->blocks;
		}

		$last_blocks = LastBlock::limited()->orderBy('found_at', 'desc')->get()->chunk(LastBlock::LIMIT / 2);

		return view($this->resolveView('home.index', 'home.text'), [
			'network' => $last_log ?? new Network,
			'hashrate_chart' => $hashrate_chart,
			'hashrate_change' => $hashrate_change,
			'blocks_chart' => $blocks_chart,
			'last_blocks' => $last_blocks,
			'new_blocks' => $new_blocks,
		]);
	}
}
