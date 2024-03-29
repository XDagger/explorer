<?php namespace App\Http\Controllers\Api;

use App\Xdag\Block\MainBlock;

class MainBlocksController extends Controller
{
	public function index()
	{
		$mainBlocks = MainBlock::orderBy('height', 'asc')->limit(20)->get(); // legacy API returned last main blocks in asc order

		return response()->json([
			'limit'	 => 20,
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
