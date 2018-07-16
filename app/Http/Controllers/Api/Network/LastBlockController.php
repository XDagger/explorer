<?php
namespace App\Http\Controllers\Api\Network;

use App\Http\Controllers\Api\Controller;

use App\Modules\LastBlock\LastBlock;
use App\Xdag\XdagInterface;

class LastBlockController extends Controller
{
	protected $xdag;

	public function __construct(XdagInterface $xdag)
	{
		$this->xdag = $xdag;
	}

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
