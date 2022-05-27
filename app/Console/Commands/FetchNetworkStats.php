<?php namespace App\Console\Commands;

use App\Xdag\Node;
use App\Xdag\Network\Stat;
use App\Xdag\Block\MainBlock;
use Illuminate\Console\Command;
use Illuminate\Database\QueryException;

class FetchNetworkStats extends Command
{
	protected $signature = 'network:fetch-stats';
	protected $description = 'Fetches latest network stats and removes stats older than 3 days.';

	public function handle()
	{
		$status = Node::callRpc('xdag_getStatus')['result'];

		Stat::create([
			'synchronized' => Node::callRpc('xdag_syncing')['result']['isSyncDone'],
			'version' => Node::callRpc('xdag_protocolVersion')['result'],
			'network_type' => Node::callRpc('xdag_netType')['result'],

			'blocks' => $status['nblock'],
			'network_blocks' => $status['totalNblocks'],

			'main_blocks' => $status['nmain'],
			'network_main_blocks' => $status['totalNmain'],

			'difficulty' => substr($status['curDiff'], 2),
			'network_difficulty' => substr($status['netDiff'], 2),

			'supply' => $status['ourSupply'],
			'network_supply' => $status['netSupply'],

			'block_reward' => Node::callRpc('xdag_getRewardByNumber', [(int) $status['nmain']])['result'],

			'hashrate' => $status['hashRateOurs'],
			'network_hashrate' => $status['hashRateTotal'],
			'connections' => [], // FIXME

			'created_at' => now(),
		]);

		Stat::where('created_at', '<', now()->subDays(3))->delete();

		$mainBlocks = Node::callRpc('xdag_getBlocksByNumber', [20]);
		$mainBlocks = $mainBlocks['result'];

		if ($mainBlocks) {
			foreach ($mainBlocks as $mainBlock) {
				try {
					MainBlock::create([
						'height' => $mainBlock['height'],
						'address' => $mainBlock['address'],
						'balance' => $mainBlock['balance'],
						'remark' => $mainBlock['remark'],
						'created_at' => timestampToCarbon($mainBlock['blockTime']),
					]);
				} catch (QueryException $ex) {} // block already imported
			}

			MainBlock::where('height', '<', $mainBlock['height'])->delete();
		}

		$this->info('Completed successfully.');
		return 0;
	}
}
