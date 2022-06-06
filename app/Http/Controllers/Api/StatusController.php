<?php namespace App\Http\Controllers\Api;

use App\Xdag\Network\Stat;

class StatusController extends Controller
{
	public function index()
	{
		$stat = Stat::orderBy('id', 'desc')->first();

		if (!$stat)
			return response()->json(['error' => 'synchronizing', 'message' => 'Block explorer is currently synchronizing.'], 503);

		return response()->json([
			'version' => $stat->version,
			'state' => $stat->synchronized ? 'Synchronized with the main network. Normal operation.' : 'Connected to the main network. Synchronizing.',
			'stats' => [
				'hosts' => [count($stat->connections), count($stat->connections)],
				'blocks' => [$stat->blocks, $stat->network_blocks],
				'main_blocks' => [$stat->main_blocks, $stat->network_main_blocks],
				'extra_blocks' => 0,
				'orphan_blocks' => 0,
				'wait_sync_blocks' => 0,
				'chain_difficulty' => [$stat->difficulty, $stat->network_difficulty],
				'xdag_supply' => [(int) $stat->supply, (int) $stat->network_supply],
				'4_hr_hashrate_mhs' => [round($stat->hashrate / 1024 / 1024, 2), round($stat->network_hashrate / 1024 / 1024, 2)],
				'hashrate' => [$stat->hashrate, $stat->network_hashrate],
			],
			'net_conn' => array_map(function ($connection) {
				return [
					'host' => $connection['nodeAddress'],
					'seconds' => floor($connection['connectTime'] / 1000) - time(),
					'in_out_bytes' => [$connection['inBound'] * 512, $connection['outBound'] * 512],
					'in_out_packets' => [$connection['inBound'], $connection['outBound']],
					'in_out_dropped' => [0, 0],
				];
			}, $stat->connections),
			'date' => $stat->created_at->format('Y-m-d H:i:s'),
		]);
	}
}
