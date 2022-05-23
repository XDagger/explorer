<?php namespace App\Http\Controllers;

use App\Xdag\Block\Cache;

class BlockController extends Controller
{
	public function index(string $id)
	{
		//return redirect()->back()->withError('Block was not found. Please make sure you entered correct address, block hash or height.');
		//return redirect()->back()->withError('Unable to retrieve block data, please try again later.');

		if (strlen($id) < 32 && !ctype_digit($id))
			$id = str_pad($search, 32, '/');

		try {
			$block = Cache::getBlock($id);
		} catch (\Illuminate\Database\Eloquent\ModelNotFoundException $ex) {
			return redirect()->back()->withError('Unable to retrieve block data, please try again later.');
		}

		dd($block);

		return view('block.index', [
			'block' => $block,
			'addressFiltersValidation' => $address_filters_validation,
			'addressFilters' => $address_filters,
			'showRemarkFilter' => $this->xdag->versionGreaterThan('0.2.5'),
			'addressPagination' => $address_paginator,
			'transactionFiltersValidation' => $transaction_filters_validation,
			'transactionFilters' => $transaction_filters,
			'transactionPagination' => $transaction_paginator,
			'balanceChange' => $block->getBalanceChange(),
			'earningChange' => $block->getEarningsChange(),
			'spendingChange' => $block->getSpendingsChange(),
		]);
	}
}
