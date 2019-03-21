<?php
namespace App\Http\Controllers\Api\Block;

use App\Xdag\Block\Pagination\Paginator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

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

	public function show($address_or_hash)
	{
		if (strlen($address_or_hash) < 32)
			$address_or_hash = str_pad($address_or_hash, 32, '/');

		if (! Validator::isAddress($address_or_hash) && ! Validator::isBlockHash($address_or_hash)) {
			return $this->response()->error('invalid_input', 'Incorrect address or block hash.', Response::HTTP_UNPROCESSABLE_ENTITY);
		}

		$transaction_paginator = new Paginator(max(1, request()->input('transactions_per_page', 10000000000000)), 'transactions_page');
		$address_paginator = new Paginator(max(1, request()->input('addresses_per_page', 10000000000000)), 'addresses_page');

		$parser = new OutputParser($transaction_paginator, $address_paginator);
		$parser->setCallback([new OutputStream, 'stream']);

		try {
			$callback = function () use ($address_or_hash, $parser) {
				$this->xdag->getBlock($address_or_hash, $parser);
			};

			return StreamedResponse::create($callback, Response::HTTP_OK, ['Content-Type' => 'application/json']);
		} catch (\InvalidArgumentException $e) {
			return $this->response()->error('block_not_found', 'Block was not found.', Response::HTTP_NOT_FOUND);
		}
	}
}
