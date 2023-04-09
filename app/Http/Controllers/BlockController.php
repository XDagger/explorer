<?php namespace App\Http\Controllers;

use App\Xdag\Block\Cache;
use App\Xdag\Block\Listing\{WalletListing, TransactionsListing};

class BlockController extends Controller
{
	public function index()
	{
		$id = substr(ltrim(request()->getRequestUri(), '/'), 6 /* block/ */);

		try {
			$block = Cache::getBlock($id);
		} catch (\InvalidArgumentException $ex) {
			return redirect()->route('home')->withError($ex->getMessage());
		} catch (\Throwable $ex) {
			return redirect()->route('home')->withError('Unable to retrieve block data, please try again later. Message: ' . $ex->getMessage());
		}

		if (!$block->existsOnBlockchain())
			return redirect()->route('home')->withError('Block was not found. Please make sure you entered correct address, block hash or main block height.');

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
