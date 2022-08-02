<?php namespace App\Console\Commands;

use App\Xdag\Node;
use App\Xdag\Network\Stat;
use App\Xdag\Block\MainBlock;
use App\Xdag\Exceptions\XdagException;
use Illuminate\Console\Command;
use Illuminate\Database\QueryException;

class FetchNetworkStats extends Command
{
	protected $signature = 'network:fetch-stats';
	protected $description = 'Fetches latest network stats and removes stats older than 3 days.';

	public function handle()
	{
		$mainBlocks = null;

		try {
			$status = Node::callRpc('xdag_getStatus')['result'];
			$mainBlocks = Node::callRpc('xdag_getBlocksByNumber', [20]);
			$mainBlocks = $mainBlocks['result'];

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

				'hashrate' => intval($status['hashRateOurs'] * 1024),
				'network_hashrate' => intval($status['hashRateTotal'] * 1024),
				'connections' => Node::callRpc('xdag_netConnectionList')['result'],

				'created_at' => now(),
			]);
		} catch (XdagException $ex) {
			// if latest stat exists, reuse most of the values
			if ($stat = Stat::orderBy('id')->first()) {
				$data = [
					'synchronized' => false,
					'created_at' => now(),
				] + $stat->toArray();

				unset($data['id']);

				Stat::create($data);
			}
		}

		Stat::where('created_at', '<', now()->subDays(3))->delete();

		if ($mainBlocks) {
			foreach ($mainBlocks as $mainBlock) {
				MainBlock::updateOrCreate(['address' => $mainBlock['address']], [
					'height' => $mainBlock['height'],
					'balance' => $mainBlock['balance'],
					'remark' => $mainBlock['remark'] === '' ? null : $mainBlock['remark'],
					'created_at' => timestampToCarbon($mainBlock['blockTime']),
				]);
			}

			MainBlock::whereNotIn('address', collect($mainBlocks)->map(fn($mainBlock) => $mainBlock['address']))->delete();
		}

		$this->info('Completed successfully.');
		return 0;
	}
}
