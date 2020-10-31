<?php
namespace App\Modules\LastBlock\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

use App\Xdag\XdagInterface;
use App\Xdag\Block\Output\OutputParser;
use App\Xdag\Exceptions\{XdagNodeNotReadyException, XdagBlockNotFoundException};

use App\Modules\LastBlock\LastBlock;

class FetchNewLastBlocks extends Command
{
	protected $signature = 'explorer:fetch-new-last-blocks';
	protected $description = 'Fetch new last blocks from xdag and update timestamp of block creation in database.';

	protected $xdag, $parser;

	public function handle(XdagInterface $xdag, OutputParser $parser)
	{
		$this->xdag = $xdag;
		$this->parser = $parser;

		if ($this->xdag->versionGreaterThan('0.3.9'))
			$this->processMainBlocksWithHeight();
		else if ($this->xdag->versionGreaterThan('0.2.4'))
			$this->processMainBlocks();
		else
			$this->processLastBlocks();

		$this->info('FetchNewLastBlocks completed successfully.');
	}

	protected function processLastBlocks()
	{
		try {
			$list = collect($this->xdag->getLastBlocks(LastBlock::LIMIT));
		} catch (XdagNodeNotReadyException $e) {
			$this->line('Node is not ready, aborting command.');
			return;
		}

		$lastBlocksToSave = collect();

		foreach ($list as $address) {
			$block = LastBlock::firstOrNew(compact('address'));

			if (is_null($block->found_at)) {
				try {
					$blockDetails = $this->xdag->getBlock($address, $this->parser);
				} catch (XdagBlockNotFoundException $ex) {
					continue;
				}

				$block->found_at = Carbon::parse(
					$blockDetails->getProperties()->get('time')
				);
			}

			$lastBlocksToSave->push($block);
		}

		$lastBlocksToSave->each->save();
		LastBlock::whereNotIn('address', $list->values()->toArray())->delete();
	}

	protected function processMainBlocks()
	{
		try {
			$list = collect($this->xdag->getMainBlocks(LastBlock::LIMIT));
		} catch (XdagNodeNotReadyException $e) {
			$this->line('Node is not ready, aborting command.');
			return;
		}

		$mainBlocksToSave = collect();
		$addresses = [];

		foreach ($list as $line) {
			$line = preg_split('/\s+/u', $line);

			if (count($line) < 4 || $line[3] !== 'Main')
				continue;

			$address = $line[0];
			$addresses[] = $line[0];

			$block = LastBlock::firstOrNew(compact('address'));
			$block->found_at = Carbon::parse($line[1] . ' ' . $line[2]);

			array_splice($line, 0, 4);
			// will truncate multiple whitespace into one space because of preg_split before
			$remark = trim(implode(' ', $line));

			if ($remark !== '')
				$block->remark = $remark;

			$mainBlocksToSave->push($block);
		}

		$mainBlocksToSave->each->save();
		LastBlock::whereNotIn('address', $addresses)->delete();
	}

	protected function processMainBlocksWithHeight()
	{
		try {
			$list = collect($this->xdag->getMainBlocks(LastBlock::LIMIT));
		} catch (XdagNodeNotReadyException $e) {
			$this->line('Node is not ready, aborting command.');
			return;
		}

		$mainBlocksToSave = collect();
		$addresses = [];

		foreach ($list as $line) {
			$line = preg_split('/\s+/u', $line);

			if (count($line) < 5 || $line[4] !== 'Main')
				continue;

			$height = $line[0];
			$address = $line[1];
			$addresses[] = $line[1];

			$block = LastBlock::firstOrNew(compact('address'));
			$block->found_at = Carbon::parse($line[2] . ' ' . $line[3]);
			$block->height = $height;

			array_splice($line, 0, 5);
			// will truncate multiple whitespace into one space because of preg_split before
			$remark = trim(implode(' ', $line));

			if ($remark !== '')
				$block->remark = $remark;

			$mainBlocksToSave->push($block);
		}

		$mainBlocksToSave->each->save();
		LastBlock::whereNotIn('address', $addresses)->delete();
	}
}
