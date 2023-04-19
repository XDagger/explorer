<?php namespace App\Http\Controllers;

use App\Xdag\Block\Cache;
use App\Xdag\Block\Listing\{WalletListing, TransactionsListing};

class BlockController extends Controller
{
	public function index()
	{
		$id = substr(ltrim(parse_url('http://x' . request()->getRequestUri(), PHP_URL_PATH), '/'), 6 /* block/ */);

		try {
			$block = Cache::getBlock($id);
		} catch (\InvalidArgumentException $ex) {
			return redirect()->route('home', '400');
		}

		if (!$block->existsOnBlockchain())
			return redirect()->route('home', '404');

		return view('block.index', [
			'block' => $block,
			'balanceGraph' => $balanceGraph = $block->walletGraph('balance'),
			'earningsGraph' => $earningsGraph = $block->walletGraph('earnings'),
			'spendingsGraph' => $spendingsGraph = $block->walletGraph('spendings'),
			'balanceChange' => valueChange($balanceGraph['values'][count($balanceGraph['values']) - 2] ?? 0, $balanceGraph['values'][count($balanceGraph['values']) - 1] ?? 0),
			'earningsChange' => valueChange($earningsGraph['values'][count($earningsGraph['values']) - 2] ?? 0, $earningsGraph['values'][count($earningsGraph['values']) - 1] ?? 0),
			'spendingsChange' => valueChange($spendingsGraph['values'][count($spendingsGraph['values']) - 2] ?? 0, $spendingsGraph['values'][count($spendingsGraph['values']) - 1] ?? 0),
			'walletListing' => app(WalletListing::class, ['block' => $block]),
			'transactionsListing' => app(TransactionsListing::class, ['block' => $block]),
		]);
	}

	public function balance()
	{
		return view('balance.index');
	}
}
