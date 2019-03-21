<?php
namespace App\Modules\NodeStatistic;

use Exception;

class NodeStatisticRepository
{
	const NODE_CHECK_TIMEOUT = 30;

	public function getPaginatedNodes($perPage = 10, $with = [])
	{
		return NodeStatistic::with($with)
							->whereRaw('id IN (SELECT MAX(id) FROM node_statistics GROUP BY node)')
							->orderBy('is_reachable', 'desc')
							->orderBy('node', 'asc')
							->paginate($perPage);
	}

	public function removeNodeStatisticsExcept($nodes)
	{
		NodeStatistic::whereNotIn('node', (array)$nodes)->delete();
	}

	public function removeOldNodeStatistics()
	{
		NodeStatistic::where('created_at', '<=', now()->subMonths(3));
	}

	public function lastCheckAt()
	{
		return NodeStatistic::latest()->limit(1)->value('created_at');
	}

	public function uptimePercentageAndLastSeenAt(NodeStatistic $node)
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

		$reachable = $statistics->filter->is_reachable;

		$first_reachable_stat = $reachable->sortByDesc('created_at')->first();
		$last_seen_at = $first_reachable_stat ? $first_reachable_stat->reachable_at : null;

		return [
			'uptime_percentage' => round(($reachable->count() / $totalChecks) * 100, 2),
			'last_seen_at' => $last_seen_at,
		];
	}

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
