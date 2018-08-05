<?php
namespace App\Http\Controllers\Api\Network;

use App\Http\Controllers\Api\Controller;

use App\Modules\LastBlock\LastBlock;

class LastBlockController extends Controller
{
	public function show()
	{
		$lastBlocks = LastBlock::limited()->get();

		return $this->response()->make([
			'limit'	 => LastBlock::LIMIT,
			'blocks' => $lastBlocks->map(function (LastBlock $block) {
				return [
					'address' => $block->address,
					'time'	  => $block->found_at->toDateTimeString(),
				];
			}),
		]);
	}
}
