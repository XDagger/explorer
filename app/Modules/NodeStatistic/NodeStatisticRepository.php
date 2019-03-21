<?php
namespace App\Modules\NodeStatistic;

use Exception;

class NodeStatisticRepository
{
	/**
	 * Node check timeout in seconds.
	 */
	const NODE_CHECK_TIMEOUT = 10;

	/**
	 * @param array $with
	 *
	 * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
	 */
	public function getPaginatedNodes($perPage = 10, $with = [])
	{
		return NodeStatistic::with($with)
							->whereRaw('id IN (SELECT MAX(id) FROM node_statistics GROUP BY node)')
							->latest()
							->paginate($perPage);
	}

	/**
	 * Remove node statistics except passed ones
	 *
	 * @param array $nodes
	 */
	public function removeNodeStatisticsExcept($nodes)
	{
		NodeStatistic::whereNotIn('node', (array)$nodes)->delete();
	}

	/**
	 * Remove old node statistics
	 */
	public function removeOldNodeStatistics()
	{
		NodeStatistic::where('created_at', '<=', now()->subMonths(3));
	}

	/**
	 * @return \Carbon\Carbon|null
	 */
	public function lastCheckAt()
	{
		return NodeStatistic::latest()->limit(1)->value('created_at');
	}

	/**
	 * Calculate uptime percentage of node for last 3 months
	 *
	 * @param \App\Modules\NodeStatistic\NodeStatistic $node
	 *
	 * @return float|int
	 */
	public function uptimePercentage(NodeStatistic $node)
	{
		$since = now()->subMonths(3);

		if ($node->relationLoaded('statistics')) {
			$statistics = $node->statistics->filter(function (NodeStatistic $node) use ($since) {
				return $node->created_at->gte($since);
			});
		} else {
			$statistics = $node->statistics()->where('created_at', '>=', $since)->get();
		}

		$totalChecks = count($statistics);

		if ($totalChecks == 0) {
			return 0;
		}

		$reachable = $statistics->filter->is_reachable->count();

		return round(($reachable / $totalChecks) * 100, 2);
	}

	/**
	 * Check if node is reachable
	 *
	 * @param string $node
	 *
	 * @return bool
	 */
	public function isReachable($node)
	{
		[$ip, $port] = explode(':', $node);

		try {
			$socket = @fsockopen($ip, $port, $errno, $errstr, self::NODE_CHECK_TIMEOUT);
			$result = ! ! $socket;
			@fclose($socket);
			return $result;
		} catch (Exception $e) {
			return false;
		}
	}

	/**
	 * @return \Illuminate\Support\Collection
	 * @throws \Exception
	 */
	public function getNodesListFromFile()
	{
		$path = (string) config('services.xdag.whitelist_path');

		if ($path === '' || !file_exists($path)) {
			throw new Exception('Nodes list file does not exist.');
		}

		$contents = file_get_contents($path);
		$lines	= explode("\n", $contents);

		$nodes = [];

		foreach ($lines as $line) {
			$parts = explode(':', $line);

			if (count($parts) != 2)
				continue;

			$ip = trim($parts[0]);
			$port = trim($parts[1]);

			if ($ip === '' || $port === '')
				continue;

			$validation = validator(compact('ip', 'port'), [
				'ip'   => 'required|ip',
				'port' => 'required|int|min:1|max:65535',
			]);

			if ($validation->passes()) {
				$nodes[] = $ip . ':' . $port;
			}
		}

		return collect($nodes);
	}
}
