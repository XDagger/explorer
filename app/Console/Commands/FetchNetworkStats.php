<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Xdag\Block\MainBlock;
use App\Xdag\Exceptions\XdagException;
use App\Xdag\Network\Stat;
use App\Xdag\Node;
use Illuminate\Console\Command;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class FetchNetworkStats extends Command
{
    protected $signature = 'network:fetch-stats';

    protected $description = 'Fetches latest network stats and removes stats older than 3 days.';

    public function handle()
    {
        try {
            $status = Node::callRpc('xdag_getStatus')['result'];
            $mainBlocks = Node::callRpc('xdag_getBlocksByNumber', [config('explorer.main_blocks_count', 20)]);
            $mainBlocks = $mainBlocks['result'];
            $statData = [
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
                'connections' => Node::callRpc('xdag_netConnectionList')['result'] ?? [],

                'created_at' => now(),
            ];

            if (config('explorer.hashrate_estimation.enabled', false)) {
                $estimatedNetworkHashrate = $this->estimateNetworkHashrate(config('explorer.hashrate_estimation.pool_api_url'), config('explorer.hashrate_estimation.main_block_remarks'), $mainBlocks);

                if ($estimatedNetworkHashrate !== null) {
                    $statData['network_hashrate'] = $estimatedNetworkHashrate;
                }
            }
        } catch (XdagException) {
            // if latest stat exists, reuse most of the values
            if ($stat = Stat::orderByDesc('id')->first()) {
                $statData = [
                    'synchronized' => false,
                    'created_at' => now(),
                ] + $stat->toArray();

                unset($statData['id']);
            }
        }

        if (isset($statData)) {
            Stat::create($statData);
        }

        Stat::where('created_at', '<', now()->subDays(3))->delete();

        if (isset($mainBlocks)) {
            foreach ($mainBlocks as $mainBlock) {
                MainBlock::updateOrCreate(['address' => $mainBlock['address']], [
                    'height' => $mainBlock['height'],
                    'balance' => $mainBlock['balance'],
                    'remark' => $mainBlock['remark'] === '' ? null : $mainBlock['remark'],
                    'created_at' => timestampToCarbon($mainBlock['blockTime']),
                ]);
            }

            MainBlock::whereNotIn('address', collect($mainBlocks)->map(fn ($mainBlock) => $mainBlock['address']))->delete();
        }

        $this->info('Completed successfully.');

        return 0;
    }

    protected function estimateNetworkHashrate(string $poolApiUrl, array $mainBlockRemarks, array $mainBlocks): ?int
    {
        if (! $mainBlockRemarks || ! $mainBlocks) {
            return null;
        }

        $poolBlocksCount = 0;

        foreach ($mainBlocks as $mainBlock) {
            if (in_array((string) $mainBlock['remark'], $mainBlockRemarks)) {
                $poolBlocksCount++;
            }
        }

        if ($poolBlocksCount === 0) {
            return null;
        }

        try {
            $apiResponse = Http::acceptJson()
                ->withHeaders(['Connection' => 'close'])
                ->withoutRedirecting()
                ->timeout(5)
                ->get($poolApiUrl)
                ->json();
        } catch (ConnectionException) {
            return null;
        }

        if (! is_array($apiResponse) || ! isset($apiResponse['hashrate']) || ! is_int($apiResponse['hashrate'])) {
            return null;
        }

        return intval($apiResponse['hashrate'] / ($poolBlocksCount / count($mainBlocks)));
    }
}
