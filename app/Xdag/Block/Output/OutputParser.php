<?php
namespace App\Xdag\Block\Output;

use Carbon\Carbon;

use App\Xdag\Block\Block;
use App\Xdag\Exceptions\{XdagException, XdagBlockNotFoundException};

use App\Xdag\Block\Pagination\Paginator;

use App\Xdag\Block\Filters\AddressFilters;
use App\Xdag\Block\Filters\TransactionFilters;

use App\Xdag\Block\Line\LineParser;
use App\Support\ValueChangeCalculator;

class OutputParser
{
	protected $transactionPaginator, $addressPaginator;
	protected $transactionFilters, $addressFilters;
	protected $parser, $change;
	protected $user_callback;
	protected $flipped;

	public function __construct(
		?Paginator $transactionPaginator = null,
		?Paginator $addressPaginator = null,
		?TransactionFilters $transactionFilters = null,
		?AddressFilters $addressFilters = null
	) {
		$this->transactionPaginator = $transactionPaginator;
		$this->addressPaginator = $addressPaginator;
		$this->transactionFilters = $transactionFilters;
		$this->addressFilters = $addressFilters;

		$this->parser = resolve(LineParser::class);
		$this->change = resolve(ValueChangeCalculator::class);
	}

	public function setFlippedOutput($flipped)
	{
		$this->flipped = (boolean) $flipped;
	}

	public function getBlockFromOutput($output)
	{
		$properties = $transactions = $addresses = [];
		$transaction_number = $address_number = $total_earnings = $total_spendings = $total_balance = '0.000000000';
		$total_fees = $total_inputs = $total_outputs = '0.000000000';
		$total_fees_count = $total_inputs_count = $total_outputs_count = 0;
		$filtered_earnings = $filtered_spendings = '0.000000000';
		$filtered_fees = $filtered_inputs = $filtered_outputs = '0.000000000';
		$total_transactions_count = $total_addresses_count = 0;

		$state = 'properties';
		bcscale(9);

		$earnings_graph = [];
		$date = now()->subDays(6);

		for ($i = 6; $i >= 0; $i--) {
			$earnings_graph[$date->format('Y-m-d')] = 0;
			$date->addDays(1);
		}

		$spendings_graph = $balances_graph = $earnings_graph;

		$earnings_base = $spendings_base = $balance_base = null;
		$earnings_change = $spendings_change = $balance_change = 0;

		$last_day_boundary = now()->subDays(1);

		foreach ($this->sortedOutput($output) as $line) {
			switch ($state) {
				case 'properties':
					if (!$this->parser->blockExists($line)) {
						if ($this->user_callback)
							return $this->callback('not_found', '');
						else
							throw new XdagBlockNotFoundException;
					}

					if ($this->parser->shouldProceedToTransactions($line)) {
						$state = 'transactions';
						$this->callback('state', $state);
						break;
					}

					if ($property = $this->parser->parseProperty($line)) {
						$this->callback('property', $property);
						$properties = array_merge($properties, $property);
					}

					break;

				case 'transactions':
					if ($this->parser->shouldProcceedToAddresses($line)) {
						$state = 'addresses';
						$this->callback('state', $state);
						break;
					}

					if (!$this->parser->isValidTransaction($line))
						break;

					$total_transactions_count++;

					$transaction = $this->parser->parseTransaction($line);

					if ($transaction['direction'] == 'output') {
						$total_outputs = bcadd($total_outputs, $transaction['amount']);
						$total_outputs_count++;
					} else if ($transaction['direction'] == 'fee') {
						$total_fees = bcadd($total_fees, $transaction['amount']);
						$total_fees_count++;
					} else if ($transaction['direction'] == 'input') {
						$total_inputs = bcadd($total_inputs, $transaction['amount']);
						$total_inputs_count++;
					}

					if (!is_null($this->transactionFilters) && !$this->transactionFilters->forTransactionData($line)->passes())
						break;

					if ($transaction['direction'] == 'output')
						$filtered_outputs = bcadd($filtered_outputs, $transaction['amount']);
					else if ($transaction['direction'] == 'fee')
						$filtered_fees = bcadd($filtered_fees, $transaction['amount']);
					else if ($transaction['direction'] == 'input')
						$filtered_inputs = bcadd($filtered_inputs, $transaction['amount']);

					$this->handlePaginatorSetup($this->transactionPaginator, ++$transaction_number);

					if ($this->shouldSaveTransaction($transaction_number)) {
						$this->callback('transaction', $transaction);
						$transactions[] = $transaction;
					}

					break;

				case 'addresses':
					if (!$this->parser->isValidAddress($line))
						break;

					$total_addresses_count++;

					$address = $this->parser->parseAddress($line, $properties['Height'] ?? null);

					$date = Carbon::parse($address['time']);
					$date_index = $date->format('Y-m-d');

					$operator = $this->flipped ? 'lte' : 'gte';

					if ($earnings_base === null && $date->$operator($last_day_boundary)) {
						$earnings_base = $total_earnings;
						$spendings_base = $total_spendings;
						$balance_base = $total_balance;
					}

					$operator = $this->flipped ? 'subDays' : 'addDays';

					if ($address['direction'] == 'output') {
						$total_spendings = bcadd($total_spendings, $address['amount']);
						$total_balance = bcsub($total_balance, $address['amount']);

						if (isset($spendings_graph[$date_index])) {
							$spendings_graph[$date_index] = bcadd($spendings_graph[$date_index], $address['amount']);

							while (isset($balances_graph[$date_index])) {
								$balances_graph[$date_index] = $total_balance;

								$date->$operator(1);
								$date_index = $date->format('Y-m-d');
							}
						}
					} else {
						$total_earnings = bcadd($total_earnings, $address['amount']);
						$total_balance = bcadd($total_balance, $address['amount']);

						if (isset($earnings_graph[$date_index])) {
							$earnings_graph[$date_index] = bcadd($earnings_graph[$date_index], $address['amount']);

							while (isset($balances_graph[$date_index])) {
								$balances_graph[$date_index] = $total_balance;

								$date->$operator(1);
								$date_index = $date->format('Y-m-d');
							}
						}
					}

					if (!is_null($this->addressFilters) && !$this->addressFilters->forAddressData($line)->passes())
						break;

					if ($address['direction'] == 'output')
						$filtered_spendings = bcadd($filtered_spendings, $address['amount']);
					else
						$filtered_earnings = bcadd($filtered_earnings, $address['amount']);

					$this->handlePaginatorSetup($this->addressPaginator, ++$address_number);

					if ($this->shouldSaveAddress($address_number)) {
						$addresses[] = $address;
						$this->callback('address', $address);
					}

					break;
			}
		}

		if ($state !== 'addresses') {
			if ($this->user_callback)
				return $this->callback('invalid_markup', '');
			else
				throw new XdagException('Invalid block markup.');
		}

		if ($earnings_base === null) {
			// block output never crossed last day boundary
			$earnings_base = $total_earnings;
			$spendings_base = $total_spendings;
			$balance_base = $total_balance;
		}

		// fix up balances graph for 0.2.5+ flipped block command output
		if ($this->flipped) {
			$balances_graph = array_reverse($balances_graph);
			$previous = $diff = null;

			foreach ($balances_graph as $date => $balance) {
				if ($previous === null) {
					$balances_graph[$date] = $total_balance;
					$previous = $balance;
					$diff = bcsub($total_balance, $balance);
					continue;
				}

				$balances_graph[$date] = $diff;
				$diff = bcadd($previous, bcsub($diff, $balance));
				$previous = $balance;
			}

			$balances_graph = array_reverse($balances_graph);
		}

		$block = new Block([
			'properties' => $properties,
			'transactions' => $transactions,
			'addresses' => $addresses,

			'earnings' => $earnings_graph,
			'spendings' => $spendings_graph,
			'balances' => $balances_graph,

			'earnings_change' => $this->change->calculate($this->flipped ? $total_earnings - $earnings_base : $earnings_base, $total_earnings),
			'spendings_change' => $this->change->calculate($this->flipped ? $total_spendings - $spendings_base : $spendings_base, $total_spendings),
			'balance_change' => $this->change->calculate($this->flipped ? $total_balance - $balance_base : $balance_base, $total_balance),

			'total_earnings' => $total_earnings,
			'total_spendings' => $total_spendings,

			'total_fees' => $total_fees,
			'total_inputs' => $total_inputs,
			'total_outputs' => $total_outputs,

			'total_fees_count' => $total_fees_count,
			'total_inputs_count' => $total_inputs_count,
			'total_outputs_count' => $total_outputs_count,

			'filtered_earnings' => $filtered_earnings,
			'filtered_spendings' => $filtered_spendings,

			'filtered_fees' => $filtered_fees,
			'filtered_inputs' => $filtered_inputs,
			'filtered_outputs' => $filtered_outputs,

			'total_transactions_count' => $total_transactions_count,
			'total_addresses_count' => $total_addresses_count,
		]);

		if ($this->transactionPaginator) {
			$this->callback('extras', ['transactions_pagination' => $this->transactionPaginator->toArray()]);
		}

		if ($this->addressPaginator) {
			$this->callback('extras', ['addresses_pagination' => $this->addressPaginator->toArray()]);
		}

		$this->callback('block', $block);

		return $block;
	}

	public function setCallback(callable $callback)
	{
		$this->user_callback = $callback;
	}

	/*
	 * in XDAG version < 0.2.5
	 *     - the earning line is first, followed by entries sorted by time ASC (correct overall order)
	 *     - class variable $flipped will be FALSE - we simply yield line by line
	 * in XDAG version = 0.2.5
	 *     - the earning line is first, followed by entries sorted by time DESC (incorrect overall order)
	 *     - class variable $flipped will be TRUE - we manually yield the earning line last
	 * in XDAG version >= 0.3.0
	 *     - the earning line is LAST, followed by entries sorted by time DESC (correct overall order)
	 *     - class variable $flipped will be TRUE - we manually yield the earning line last, but since
	 *       the line is already the last line in the output, this function has no effect.
	 *
	 * Summary: this function preserves correct entries order for "block as address" part of the output
	 * for 0.2.5, but also behaves correctly in other versions.
	 *
	 */
	protected function sortedOutput($output)
	{
		$earning = null;

		foreach ($output as $line) {
			if ($this->flipped && substr(trim($line), 0, 8) === 'earning:') {
				$earning = $line;
			} else {
				yield $line;
			}
		}

		if ($earning !== null)
			yield $earning;
	}

	protected function shouldSaveTransaction($transaction_number)
	{
		if (! is_null($this->transactionPaginator)) {
			if ($transaction_number < $this->transactionPaginator->start() || $transaction_number > $this->transactionPaginator->end()) {
				return false;
			}
		}

		return true;
	}

	protected function shouldSaveAddress($address_number)
	{
		if (! is_null($this->addressPaginator)) {
			if ($address_number < $this->addressPaginator->start() || $address_number > $this->addressPaginator->end()) {
				return false;
			}
		}

		return true;
	}

	protected function handlePaginatorSetup(?Paginator $paginator, $address_number)
	{
		if (is_null($paginator))
			return;

		$paginator->setTotalNumberOfItems($address_number);

		// set has more pages to true since current address number is greater than paginator end for current page
		if ($address_number > $paginator->end())
			$paginator->setHasMorePages(true);
	}

	protected function callback($message, $data = null)
	{
		if (!$this->user_callback)
			return;

		call_user_func_array($this->user_callback, [$message, $data]);
	}
}
