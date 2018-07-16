<?php
namespace App\Modules\Network\Charts;

use App\Xdag\Hashpower;
use App\Modules\Network\Network;

class HashrateLastDaysChart
{
	protected $chart;

	public function __construct()
	{
		$this->chart = [];
		$logs = Network::selectRaw('avg(hashrate) hashrate, DATE_FORMAT(created_at, "%Y-%m-%d %H:00:00") created_at')->groupBy('created_at')->orderBy('created_at')->get();

		foreach ($logs as $log) {
			$this->chart[$log->created_at->format('m-d H:00')] = round($log->hashrate / Hashpower::THS, 2);
		}

		$this->chart = collect($this->chart);
	}

	public function keys()
	{
		return $this->chart->keys();
	}

	public function values()
	{
		return $this->chart->values();
	}
}
