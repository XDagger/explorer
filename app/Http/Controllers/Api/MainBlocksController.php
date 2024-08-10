<?php namespace App\Http\Controllers\Api;

use App\Xdag\Block\MainBlock;

class MainBlocksController extends Controller
{
	public function index()
	{
		$mainBlocks = MainBlock::orderBy('height', 'asc')->get(); // legacy API returned last main blocks in asc order

		return response()->json([
			'limit'	 => $mainBlocks->count(),
			'blocks' => $mainBlocks->map(function (MainBlock $block) {
				return [
					'height'  => strval($block->height),
					'address' => $block->address,
					'time'	  => $block->created_at->toDateTimeString(),
					'remark'  => $block->remark,
				];
			}),
		]);
	}
}
