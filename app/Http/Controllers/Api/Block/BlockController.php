<?php
namespace App\Http\Controllers\Api\Block;

use App\Xdag\Block\Filters\AddressFiltersBuilder;
use App\Xdag\Block\Filters\AddressFiltersValidation;
use App\Xdag\Block\Filters\TransactionFiltersBuilder;
use App\Xdag\Block\Filters\TransactionFiltersValidation;
use App\Xdag\Block\Pagination\Paginator;

use App\Http\Controllers\Api\Controller;

use App\Xdag\XdagInterface;

use App\Xdag\Block\Validation\Validator;
use App\Xdag\Block\Output\{OutputParser, OutputStream};

class BlockController extends Controller
{
	protected $xdag;

	public function __construct(XdagInterface $xdag)
	{
		$this->xdag = $xdag;
	}

	public function show($search)
	{
		if (strlen($search) < 32 && !ctype_digit($search))
			$search = str_pad($search, 32, '/');

		if (!Validator::isAddress($search) && !Validator::isBlockHash($search) && !Validator::isHeight($search)) {
			return $this->response()->error('invalid_input', 'Incorrect address, block hash or height.', 422);
		}

		$transaction_paginator = new Paginator(max(1, request()->input('transactions_per_page', 10000000000000)), 'transactions_page');
		$address_paginator = new Paginator(max(1, request()->input('addresses_per_page', 10000000000000)), 'addresses_page');

		$transaction_filters_validation = new TransactionFiltersValidation(true);
		$transaction_filters = (new TransactionFiltersBuilder())->fromApi($transaction_filters_validation->data());

		$address_filters_validation = new AddressFiltersValidation(true);
		$address_filters = (new AddressFiltersBuilder())->fromApi($address_filters_validation->data());

		$parser = new OutputParser($transaction_paginator, $address_paginator, $transaction_filters, $address_filters);
		$parser->setCallback([new OutputStream, 'stream']);

		try {
			return response()->stream(function () use ($search, $parser) {
				$this->xdag->getBlock($search, $parser);
			}, 200, ['Content-Type' => 'application/json']);
		} catch (\InvalidArgumentException $e) {
			return $this->response()->error('block_not_found', 'Block was not found.', 404);
		}
	}
}
