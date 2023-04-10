<?php namespace App\Http\Controllers\Api;

use App\Xdag\Block\Cache;
use App\Xdag\Block\Listing\{Listing, WalletListing, TransactionsListing};
use Bcn\Component\Json\Writer as JsonWriter;
use Illuminate\Support\Arr;

class BlockController extends Controller
{
	public function index()
	{
		$id = substr(ltrim(parse_url('http://x' . request()->getRequestUri(), PHP_URL_PATH), '/api'), 6 /* block/ */);

		try {
			$block = Cache::getBlock($id);
		} catch (\InvalidArgumentException $ex) {
			return response()->json(['error' => 'invalid_input', 'message' => $ex->getMessage()], 422);
		} catch (\Throwable $ex) {
			return response()->json(['error' => 'internal_error', 'message' => $ex->getMessage()], 500);
		}

		if (!$block->existsOnBlockchain())
			return response()->json(['error' => 'invalid_input', 'message' => 'Incorrect address, block hash or main block height.'], 422);

		return response()->stream(function () use ($block) {
			$fileHandle = fopen('php://output', 'w');
			$writer = new JsonWriter($fileHandle);
			$writer->enter(JsonWriter::TYPE_OBJECT);

			if ($block->height > 0)
				$writer->write('height', $block->height);

			$writer->write('time', $block->created_at->format('Y-m-d H:i:s.v'));
			$writer->write('timestamp', $block->timestamp);
			$writer->write('flags', $block->flags);
			$writer->write('state', $block->state);
			$writer->write('file_pos', '');
			$writer->write('file', '');
			$writer->write('hash', $block->hash);
			$writer->write('remark', $block->remark);
			$writer->write('difficulty', $block->difficulty);
			$writer->write('balance_address', $block->address);
			$writer->write('balance', $block->balance);

			$directionsAmount = [];
			$transactionOutputCallback = function (Listing $listing, $builder, int $page, int $perPage) use ($writer, &$directionsAmount) {
				$skip = ($page - 1) * $perPage;
				$max = $page * $perPage;

				while (true) {
					$transactions = $builder->limit(1000)->skip($skip)->get();

					if (!$transactions->count())
						break;

					foreach ($transactions as $transaction) {
						if (!isset($directionsAmount[$transaction->direction]))
							$directionsAmount[$transaction->direction] = ltrim($transaction->amount, '-');
						else
							$directionsAmount[$transaction->direction] = bcadd($directionsAmount[$transaction->direction], ltrim($transaction->amount, '-'), 9);

						$data = [
							'direction' => $transaction->direction,
							'address' => $transaction->address,
							'amount' => ltrim($transaction->amount, '-'),
							'time' => optional($transaction->created_at)->format('Y-m-d H:i:s.v'),
							'remark' => $transaction->remark,
						];

						if ($listing instanceof TransactionsListing)
							unset($data['time'], $data['remark']);

						$writer->write(null, $data);

						if (++$skip >= $max)
							break 2;
					}
				}
			};

			$writer->enter('block_as_transaction', JsonWriter::TYPE_ARRAY);
			$transactionsListing = app(TransactionsListing::class, ['block' => $block, 'defaultPageSize' => 10000000000000]);
			$transactionsListing->getWithCallback($transactionOutputCallback);
			$writer->leave();

			$writer->enter('block_as_address', JsonWriter::TYPE_ARRAY);
			$mode = 'wallet';
			$directionsAmountTransactions = json_decode(json_encode($directionsAmount), true);
			$directionsAmount = [];
			$walletListing = app(WalletListing::class, ['block' => $block, 'defaultPageSize' => 10000000000000]);
			$walletListing->getWithCallback($transactionOutputCallback);
			$writer->leave();

			if (!$block->isTransactionBlock()) {
				// wallet, main or snapshot block
				$balanceGraph = $block->walletGraph('balance');
				$earningsGraph = $block->walletGraph('earnings');
				$spendingsGraph = $block->walletGraph('spendings');

				$writer->write('balances_last_week', array_combine($balanceGraph['labels'], $balanceGraph['values']));
				$writer->write('earnings_last_week', array_combine($earningsGraph['labels'], $earningsGraph['values']));
				$writer->write('spendings_last_week', array_combine($spendingsGraph['labels'], $spendingsGraph['values']));

				$writer->write('balance_change_last_24_hours', bcsub($balanceGraph['values'][count($balanceGraph['values']) - 1] ?? '0.000000000', $balanceGraph['values'][count($balanceGraph['values']) - 2] ?? '0.000000000', 9));
				$writer->write('earnings_change_last_24_hours', bcsub($earningsGraph['values'][count($earningsGraph['values']) - 1] ?? '0.000000000', $earningsGraph['values'][count($earningsGraph['values']) - 2] ?? '0.000000000', 9));
				$writer->write('spendings_change_last_24_hours', bcsub($spendingsGraph['values'][count($spendingsGraph['values']) - 1] ?? '0.000000000', $spendingsGraph['values'][count($spendingsGraph['values']) - 2] ?? '0.000000000', 9));

				unset($balanceGraph, $earningsGraph, $spendingsGraph);

				$writer->write('total_earnings', number_format($block->transactions()->wallet()->earnings()->sum('amount'), 9, '.', ''));
				$writer->write('total_spendings', number_format($block->transactions()->wallet()->spendings()->sum(\DB::raw('ABS(amount)')), 9, '.', ''));

				$writer->write('page_earnings_sum', bcadd($directionsAmount['input'] ?? '0.000000000', $directionsAmount['earning'] ?? '0.000000000', 9));
				$writer->write('page_spendings_sum', $directionsAmount['output'] ?? '0.000000000');

				unset($directionsAmount);

				$writer->write('filtered_earnings_sum', number_format($walletListing->earningsSum(), 9, '.', ''));
				$writer->write('filtered_spendings_sum', number_format($walletListing->spendingsSum(), 9, '.', ''));
			} else {
				// transaction block
				$writer->write('total_fee', number_format($block->transactions()->transaction()->whereDirection('fee')->sum(\DB::raw('ABS(amount)')), 9, '.', ''));
				$writer->write('total_inputs', number_format($block->transactions()->transaction()->whereDirection('input')->sum('amount'), 9, '.', ''));
				$writer->write('total_outputs', number_format($block->transactions()->transaction()->whereDirection('output')->sum(\DB::raw('ABS(amount)')), 9, '.', ''));

				$writer->write('page_fee_sum', number_format($directionsAmountTransactions['fee'] ?? '0.000000000', 9, '.', ''));
				$writer->write('page_inputs_sum', number_format($directionsAmountTransactions['input'] ?? '0.000000000', 9, '.', ''));
				$writer->write('page_outputs_sum', number_format($directionsAmountTransactions['output'] ?? '0.000000000', 9, '.', ''));

				$writer->write('filtered_fee_sum', number_format($transactionsListing->feesSum(), 9, '.', ''));
				$writer->write('filtered_inputs_sum', number_format($transactionsListing->inputsSum(), 9, '.', ''));
				$writer->write('filtered_outputs_sum', number_format($transactionsListing->outputsSum(), 9, '.', ''));
			}

			$writer->write('kind', $block->type === 'Wallet' ? 'Wallet' : "{$block->type} block");

			$linksOutputCallback = function (Listing $listing, $builder, int $page, int $perPage) use ($writer) {
				$total = $builder->count();
				$pagination = $listing->paginationParameterNames();
				$usedFilters = array_combine(array_keys($f = $listing->usedFilters()), Arr::pluck($f, 'value'));

				$writer->write($pagination[0] === 'transactions_page' ? 'transactions_pagination' : 'addresses_pagination', [
					'current_page' => $page,
					'last_page' => $lastPage = max(1, ceil($total / $perPage)),
					'total' => $total,
					'per_page' => $perPage,
					'links' => [
						'prev' => $page > 1 ? asset(request()->getPathInfo() . '?' . http_build_query(array_merge($usedFilters, array_combine($pagination, [$page - 1, $perPage])))) : null,
						'next' => $page * $perPage < $total ? request()->getPathInfo() . '?' . http_build_query(array_merge($usedFilters, array_combine($pagination, [$page + 1, $perPage]))) : null,
						'first' => request()->getPathInfo() . '?' . http_build_query(array_merge($usedFilters, array_combine($pagination, [1, $perPage]))),
						'last' => asset(request()->getPathInfo() . '?' . http_build_query(array_merge($usedFilters, array_combine($pagination, [$lastPage, $perPage])))),
					],
				]);
			};

			$transactionsListing->getWithCallback($linksOutputCallback);
			$walletListing->getWithCallback($linksOutputCallback);

			$writer->leave();
			fclose($fileHandle);
		}, 200, ['Content-Type' => 'application/json']);
	}

	public function balance()
	{
		$id = substr(ltrim(parse_url('http://x' . request()->getRequestUri(), PHP_URL_PATH), '/api'), 8 /* balance/ */);

		try {
			$balance = Cache::getBalance($id);
		} catch (\InvalidArgumentException $ex) {
			return response()->json(['error' => 'invalid_input', 'message' => $ex->getMessage()], 422);
		} catch (\Throwable $ex) {
			return response()->json(['error' => 'internal_error', 'message' => $ex->getMessage()], 500);
		}

		if (!$balance->blockExists())
			return response()->json(['error' => 'invalid_input', 'message' => 'Incorrect address, block hash or main block height.'], 422);

		return response()->json(['balance' => $balance->balance]);
	}
}
