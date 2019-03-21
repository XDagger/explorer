<?php
namespace App\Modules\NodeStatistic\Commands;

use Exception;

use Illuminate\Console\Command;

use App\Modules\NodeStatistic\NodeStatistic;
use App\Modules\NodeStatistic\NodeStatisticRepository;

class CheckNodesReachability extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'explorer:check-nodes-reachability';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Check nodes reachability and store last reachable timestamp';

	/**
	 * Execute the console command.
	 *
	 * @param \App\Modules\NodeStatistic\NodeStatisticRepository $nodeStatisticRepository
	 *
	 * @return mixed
	 */
	public function handle(NodeStatisticRepository $nodeStatisticRepository)
	{
		try {
			$nodesFromFile = $nodeStatisticRepository->getNodesListFromFile();
		} catch (Exception $e) {
			$this->error($e->getMessage());

			return;
		}

		$this->info('Removing non-existing nodes...');
		$nodeStatisticRepository->removeNodeStatisticsExcept($nodesFromFile->toArray());

		$this->info('Removing old statistics...');
		$nodeStatisticRepository->removeOldNodeStatistics();

		$progressBar = $this->output->createProgressBar($nodesFromFile->count());

		$progressBar->setFormat("%message%\n %current%/%max% [%bar%] %percent:3s%%");

		foreach ($nodesFromFile as $node) {
			$progressBar->setMessage('Checking reachability and storing statistics for ' . $node);

			$isReachable = $nodeStatisticRepository->isReachable($node);

			NodeStatistic::create([
				'node'		 => $node,
				'is_reachable' => $isReachable,
				'reachable_at' => $isReachable ? now() : null,
			]);

			$progressBar->advance();
		}

		$progressBar->finish();

		$this->info("\nNode statistics successfully stored.");
	}
}
