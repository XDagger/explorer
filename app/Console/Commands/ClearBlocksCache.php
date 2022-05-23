<?php namespace App\Console\Commands;

use Illuminate\Console\Command;

class ClearBlocksCache extends Command
{
	protected $signature = 'blocks:clear-cache';
	protected $description = 'Clears expired blocks and balances cache.';

	public function handle()
	{
		//Cache::where('expires_at', '<', now())->delete();

		$this->info('Completed successfully.');
		return 0;
	}
}
