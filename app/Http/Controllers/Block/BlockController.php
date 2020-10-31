<?php
namespace App\Http\Controllers\Block;

use InvalidArgumentException;

use App\Http\Controllers\Controller;
use App\Http\Requests\BlockSearchRequest;

use App\Support\ValueChangeCalculator;

use App\Xdag\XdagInterface;

use App\Xdag\Block\Pagination\Paginator;
use App\Xdag\Exceptions\{XdagException, XdagBlockNotFoundException};

use App\Xdag\Block\Output\OutputParser;
use App\Xdag\Block\Filters\{AddressFiltersBuilder, AddressFiltersValidation, TransactionFiltersBuilder, TransactionFiltersValidation};

class BlockController extends Controller
{
	protected $xdag;

	public function __construct(XdagInterface $xdag)
	{
		$this->xdag = $xdag;
	}

	public function search(BlockSearchRequest $request)
	{
		$search = $request->input('search');
		$url = (starts_with($request->getPathInfo(), '/text') ? '/text/block/' : '/block/') . $search;
		return response('', 301)->header('Location', $url);
	}

	public function show($search, ValueChangeCalculator $change)
	{
		if (strlen($search) < 32 && !ctype_digit($search))
			$search = str_pad($search, 32, '/');

		$transaction_paginator = new Paginator(20, 'transactions-page');
		$address_paginator = new Paginator(20, 'addresses-page');

		$transaction_filters_validation = new TransactionFiltersValidation;
		$transaction_filters = (new TransactionFiltersBuilder())->fromArray($transaction_filters_validation->data());

		$address_filters_validation = new AddressFiltersValidation();
		$address_filters = (new AddressFiltersBuilder())->fromArray($address_filters_validation->data());

		$output_parser = new OutputParser($transaction_paginator, $address_paginator, $transaction_filters, $address_filters);

		try {
			$block = $this->xdag->getBlock($search, $output_parser);
		} catch (InvalidArgumentException $ex) {
			$this->notify()->error('Block was not found. Please make sure you entered correct address, block hash or height.');
			return redirect()->route('home');
		} catch (XdagBlockNotFoundException $ex) {
			$this->notify()->error('Block was not found. Please make sure you entered correct address, block hash or height.');
			return redirect()->route('home');
		} catch (XdagException $ex) {
			$this->notify()->error('Unable to retrieve block data, please try again later. Message: ' . $ex->getMessage());
			return redirect()->route('home');
		}

		if ($block === null) {
			$this->notify()->error('Unable to retrieve block data, please try again later.');
			return redirect()->route('home');
		}

		return view($this->resolveView('block.index', 'block.text-index'), [
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
