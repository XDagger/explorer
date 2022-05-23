<?php namespace App\Console\Commands;

use App\Xdag\Network\Stat;
use App\Xdag\Block\MainBlock;
use Illuminate\Console\Command;

class FetchNetworkStats extends Command
{
	protected $signature = 'network:fetch-stats';
	protected $description = 'Fetches latest network stats and removes stats older than 3 days.';

	public function handle()
	{
		// FIXME: approximate simulation for now, update when node code is ready
		Stat::create([
			'version' => '0.4.8',
			'network_type' => 'dev',
			'blocks' => time() - 1000000000,
			'main_blocks' => $mainBlocks = 2153266 + (int) (time() - 1651156077) / 64,
			'difficulty' => \Illuminate\Support\Str::random(32),
			'supply' => $mainBlocks * 512,
			'hashrate' => rand(1024 * 1024, 1024 * 1024 * 15),
			'created_at' => now(),
		]);

		MainBlock::truncate();

		for ($i = 0; $i < 20; $i++) {
			MainBlock::create([
				'height' => $mainBlocks--,
				'address' => \Illuminate\Support\Str::random(32),
				'balance' => 64,
				'remark' => \Illuminate\Support\Arr::random(['HTTPS://XDAG.ORG EQUAL', 'HTTPS://XDAG.ORG SOLO', null]),
				'created_at' => now(),
			]);
		}

		Stat::where('created_at', '<', now()->subDays(3))->delete();

		$this->info('Completed successfully.');
		return 0;
	}
}
