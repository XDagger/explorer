<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

use App\Modules\NodeStatistic\Commands\CheckNodesReachability;
use App\Modules\Network\Commands\LogNetwork;
use App\Modules\LastBlock\Commands\FetchNewLastBlocks;

class Kernel extends ConsoleKernel
{
	protected $commands = [
		LogNetwork::class,
		FetchNewLastBlocks::class,
		Commands\ClearXdagCache::class,
		CheckNodesReachability::class,
	];

	/**
	 * Define the application's command schedule.
	 *
	 * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
	 * @return void
	 */
	protected function schedule(Schedule $schedule)
	{
		$schedule->command('explorer:clear-cache')->everyMinute()->withoutOverlapping();
		$schedule->command('explorer:log-network')->everyMinute()->withoutOverlapping();
		$schedule->command('explorer:fetch-new-last-blocks')->everyMinute()->withoutOverlapping();
		$schedule->command('explorer:check-nodes-reachability')->hourly()->withoutOverlapping();
	}

	/**
	 * Register the commands for the application.
	 *
	 * @return void
	 */
	protected function commands()
	{
		$this->load(__DIR__.'/Commands');

		require base_path('routes/console.php');
	}
}
