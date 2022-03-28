<?php
namespace App\Modules\Network\Commands;

use Illuminate\Console\Command;

use App\Xdag\XdagInterface;

use App\Modules\Network\Network;

class LogNetwork extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'explorer:log-network';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Log xdag network data';

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle()
	{
		$xdag = resolve(XdagInterface::class);
		$stats = $xdag->getStats();

		Network::create([
			'blocks' => $stats['blocks'][1],
			'main_blocks' => $stats['main_blocks'][1],
			'difficulty' => $stats['chain_difficulty'][1],
			'supply' => $stats['xdag_supply'][1],
			'hashrate' => $stats['hashrate'][1],
		]);

		Network::where('created_at', '<', now()->subDays(Network::DAYS_LIMIT))->delete();

		$status = fopen(storage_path('cache/status.json'), 'w+');
		fwrite($status, json_encode([
			'version'  => $xdag->getVersion(),
			'state'	   => trim($xdag->getState()),
			'stats'	   => $xdag->getStats(),
			'net_conn' => $xdag->getConnections(),
			'date'	   => now()->toDateTimeString(),
		], JSON_PRETTY_PRINT));
		fclose($status);

		$this->info('LogNetwork completed successfully.');
	}
}
