<?php

use Illuminate\Database\Seeder;

use App\Xdag\XdagInterface;
use App\Modules\Network\Network;

class NetworkSeeder extends Seeder
{
	/**
	 * Seed the application's database.
	 *
	 * @return void
	 */
	public function run()
	{
		$stats = resolve(XdagInterface::class)->getStats();

		// Initial data for network hashrate chart
		foreach (range(0, Network::DAYS_LIMIT) as $day) {
			Network::create([
				'main_blocks' => $stats['main_blocks'][1],
				'difficulty'  => $stats['chain_difficulty'][1],
				'supply'      => $stats['xdag_supply'][1],
				'blocks'      => $stats['blocks'][1],
				'hashrate'    => $stats['hashrate'][1],
				'created_at'  => now()->subDays($day),
			]);
		}

		// Initial data for network blocks chart
		foreach (range(0, 59, 5) as $minutes) {
			$main_blocks = $stats['main_blocks'][1];
			$supply      = $stats['xdag_supply'][1];
			$difficulty  = $stats['chain_difficulty'][1];
			$blocks      = $stats['blocks'][1];
			$hashrate    = $stats['hashrate'][1];

			Network::create([
				'main_blocks' => $main_blocks,
				'difficulty'  => $difficulty,
				'supply'      => $supply,
				'blocks'      => $blocks,
				'hashrate'    => $hashrate + $minutes,
				'created_at'  => now()->subHour(2)->startOfHour()->addMinutes($minutes),
			]);

			Network::create([
				'main_blocks' => $main_blocks,
				'difficulty'  => $difficulty,
				'supply'      => $supply,
				'blocks'      => $blocks,
				'hashrate'    => $hashrate + $minutes,
				'created_at'  => now()->subHour(1)->startOfHour()->addMinutes($minutes),
			]);
		}
	}
}
