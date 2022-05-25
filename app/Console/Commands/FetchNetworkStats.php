<?php namespace App\Console\Commands;

use App\Xdag\Node;
use App\Xdag\Network\Stat;
use App\Xdag\Block\MainBlock;
use Illuminate\Console\Command;

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
			'network_type' => 'dev', // TODO

			'blocks' => $status['nblock'],
			'network_blocks' => $status['totalNblocks'],

			'main_blocks' => $status['nmain'],
			'network_main_blocks' => $status['totalNnmain'], // FIXME (double N)

			'difficulty' => $status['curDiff'],
			'network_difficulty' => $status['netDiff'],

			'supply' => $status['supply'],
			'network_supply' => $status['supply'], // FIXME

			'block_reward' => Node::callRpc('xdag_getRewardByNumber', [(int) $status['nmain']])['result'],

			'hashrate' => $status['hashRateOurs'],
			'network_hashrate' => $status['hashRateTotal'],

			'created_at' => now(),
		]);

		Stat::where('created_at', '<', now()->subDays(3))->delete();

		$mainBlocks = Node::callRpc('xdag_getBlocksByNumber', [20]);
		$mainBlocks = $mainBlocks['result'];

		if ($mainBlocks) {
			foreach ($mainBlocks as $mainBlock) {
				MainBlock::create([
					'height' => $mainBlock['height'],
					'address' => $mainBlock['address'],
					'balance' => $mainBlock['balance'],
					'remark' => $mainBlock['remark'],
					'created_at' => timestampToCarbon($mainBlock['blockTime']),
				]);
			}

			MainBlock::where('height', '<', $mainBlock['height'])->delete();
		}

		$this->info('Completed successfully.');
		return 0;
	}
}
