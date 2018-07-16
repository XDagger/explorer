<?php
namespace App\Http\Controllers\Api\Block;

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
		if (strlen($address_or_hash) == 31)
			$address_or_hash = $address_or_hash . '/';

		if (! Validator::isAddress($address_or_hash) && ! Validator::isBlockHash($address_or_hash)) {
			return $this->response()->error('invalid_input', 'Incorrect address or block hash.', Response::HTTP_UNPROCESSABLE_ENTITY);
		}

		$parser = new OutputParser;
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
