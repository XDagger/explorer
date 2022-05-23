<?php namespace App\Http\Controllers;

use App\Xdag\Block\Cache;

class BlockController extends Controller
{
	public function index(string $id)
	{
		try {
			$block = Cache::getBlock($id);
		} catch (\InvalidArgumentException $ex) {
			return redirect()->route('home')->withError($ex->getMessage());
		} catch (\Throwable $ex) {
			return redirect()->route('home')->withError('Unable to retrieve block data, please try again later. Message: ' . $ex->getMessage());
		}

		if (!$block->existsOnBlockchain())
			return redirect()->route('home')->withError('Block was not found. Please make sure you entered correct address, block hash or height.');

		return view('block.index', [
			'block' => $block,
		]);
	}

	public function balance()
	{
		return view('balance.index');
	}
}
