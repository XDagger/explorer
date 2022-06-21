<?php namespace App\Console\Commands;

use App\Xdag\Block\{Block, Balance};
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ClearBlocksCache extends Command
{
	protected $signature = 'blocks:clear-cache';
	protected $description = 'Clears expired blocks and balances cache.';

	public function handle()
	{
		Block::where('expires_at', '<', now())->chunk(20, function ($blocks) {
			foreach ($blocks as $block) {
				DB::transaction(function () use ($block) {
					$block->transactions()->delete();
					$block->delete();
				}, 50);
			}
		});

		Balance::where('expires_at', '<', now())->chunk(20, function ($balances) {
			foreach ($balances as $balance) {
				$balance->delete();
			}
		});

		$this->info('Completed successfully.');
		return 0;
	}
}
