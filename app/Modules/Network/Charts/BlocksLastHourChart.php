<?php
namespace App\Modules\Network\Charts;

use App\Modules\Network\Network;

class BlocksLastHourChart
{
	/**
	 * @var \Illuminate\Support\Collection
	 */
	protected $chart;

	/**
	 * BlocksLastHourChart constructor.
	 */
	public function __construct()
	{
		$this->chart = [];
		$logs = Network::where('created_at', '>', now()->subHours(1))->orderBy('id', 'asc')->get();

		$diff = $last = null;
		foreach ($logs as $log) {
			if ($last === null) {
				$last = $log->blocks;
				continue;
			}

			$diff = $log->blocks - $last;
			$last = $log->blocks;
			$this->chart[$log->created_at->format('H:i')] = $diff;
		}

		$this->chart = collect($this->chart);
	}

	/**
	 * @return \Illuminate\Support\Collection
	 */
	public function keys()
	{
		return $this->chart->keys();
	}

	/**
	 * @return \Illuminate\Support\Collection
	 */
	public function values()
	{
		return $this->chart->values();
	}
}
