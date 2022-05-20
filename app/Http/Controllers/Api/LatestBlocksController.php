<?php namespace App\Http\Controllers\Api;

class LatestBlocksController extends Controller
{
	public function show()
	{
		$lastBlocks = LastBlock::limited()->get();

		return $this->response()->make([
			'limit'	 => LastBlock::LIMIT,
			'blocks' => $lastBlocks->map(function (LastBlock $block) {
				return [
					'height'  => strval($block->height),
					'address' => $block->address,
					'time'	  => $block->found_at->toDateTimeString(),
					'remark'  => strval($block->remark),
				];
			}),
		]);
	}
}
