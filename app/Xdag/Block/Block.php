<?php
namespace App\Xdag\Block;

use App\Xdag\Block\Attributes\{Properties, Transactions, Addresses, Earnings, Spendings, Balances};
use App\Modules\Network\Network;
use App\Xdag\XdagInterface;

class Block
{
	const MAIN_BLOCK_FLAGS = '1f';
	const APOLLO_FORK_HEIGHT_MAINNET = 955825;
	const APOLLO_FORK_HEIGHT_TESTNET = 196250;

	protected $properties,  $transactions, $addresses;
	protected $earnings, $spendings, $balances;
	protected $earnings_change, $spendings_change, $balance_change;
	protected $total_earnings, $total_spendings;
	protected $total_fees, $total_inputs, $total_outputs;
	protected $total_fees_count, $total_inputs_count, $total_outputs_count;
	protected $filtered_earnings, $filtered_spendings;
	protected $filtered_fees, $filtered_inputs, $filtered_outputs;
	protected $total_transactions_count, $total_addresses_count;

	public function __construct(array $data = [])
	{
		$this->properties = new Properties($data['properties'] ?? []);
		$this->transactions = new Transactions($data['transactions'] ?? []);
		$this->addresses = new Addresses($data['addresses'] ?? []);

		$this->earnings = new Earnings($data['earnings'] ?? []);
		$this->spendings = new Spendings($data['spendings'] ?? []);
		$this->balances = new Balances($data['balances'] ?? []);

		$this->earnings_change = $data['earnings_change'] ?? null;
		$this->spendings_change = $data['spendings_change'] ?? null;
		$this->balance_change = $data['balance_change'] ?? null;

		$this->total_earnings = $data['total_earnings'] ?? '0.000000000';
		$this->total_spendings = $data['total_spendings'] ?? '0.000000000';

		$this->total_fees = $data['total_fees'] ?? '0.000000000';
		$this->total_inputs = $data['total_inputs'] ?? '0.000000000';
		$this->total_outputs = $data['total_outputs'] ?? '0.000000000';

		$this->total_fees_count = $data['total_fees_count'] ?? 0;
		$this->total_inputs_count = $data['total_inputs_count'] ?? 0;
		$this->total_outputs_count = $data['total_outputs_count'] ?? 0;

		$this->filtered_earnings = $data['filtered_earnings'] ?? '0.000000000';
		$this->filtered_spendings = $data['filtered_spendings'] ?? '0.000000000';

		$this->filtered_fees = $data['filtered_fees'] ?? '0.000000000';
		$this->filtered_inputs = $data['filtered_inputs'] ?? '0.000000000';
		$this->filtered_outputs = $data['filtered_outputs'] ?? '0.000000000';

		$this->total_transactions_count = $data['total_transactions_count'] ?? 0;
		$this->total_addresses_count = $data['total_addresses_count'] ?? 0;
	}

	public static function getReward()
	{
		$log = Network::orderBy('id', 'desc')->limit(1)->first();

		if (!$log)
			return 1024;

		return $log->main_blocks > static::getApolloForkHeight() ? 128 : 1024;
	}

	public static function getApolloForkHeight(&$is_testnet = null)
	{
		$xdag = app(XdagInterface::class);
		$is_testnet = $xdag->isTestnet();

		return $is_testnet ? static::APOLLO_FORK_HEIGHT_TESTNET : static::APOLLO_FORK_HEIGHT_MAINNET;
	}

	public function isMainBlock()
	{
		return $this->properties->has('flags') && $this->properties->get('flags') === self::MAIN_BLOCK_FLAGS;
	}

	public function isTransactionBlock()
	{
		return !$this->isMainBlock() && $this->total_addresses_count == 0 && $this->total_transactions_count > 1; // "fee" transaction is always present
	}

	public function getProperties()
	{
		return $this->properties;
	}

	public function getTransactions()
	{
		return $this->transactions;
	}

	public function getAddresses()
	{
		return $this->addresses;
	}

	public function getEarnings()
	{
		return $this->earnings;
	}

	public function getSpendings()
	{
		return $this->spendings;
	}

	public function getBalances()
	{
		return $this->balances;
	}

	public function getBalance()
	{
		return $this->properties->get('balance');
	}

	public function getEarningsChange()
	{
		return $this->earnings_change;
	}

	public function getSpendingsChange()
	{
		return $this->spendings_change;
	}

	public function getBalanceChange()
	{
		return $this->balance_change;
	}

	public function getTotalEarnings()
	{
		return $this->total_earnings;
	}

	public function getTotalSpendings()
	{
		return $this->total_spendings;
	}

	public function getTotalFees()
	{
		return $this->total_fees;
	}

	public function getTotalInputs()
	{
		return $this->total_inputs;
	}

	public function getTotalOutputs()
	{
		return $this->total_outputs;
	}

	public function getTotalFeesCount()
	{
		return $this->total_fees_count;
	}

	public function getTotalInputsCount()
	{
		return $this->total_inputs_count;
	}

	public function getTotalOutputsCount()
	{
		return $this->total_outputs_count;
	}

	public function getFilteredEarnings()
	{
		return $this->filtered_earnings;
	}

	public function getFilteredSpendings()
	{
		return $this->filtered_spendings;
	}

	public function getFilteredFees()
	{
		return $this->filtered_fees;
	}

	public function getFilteredInputs()
	{
		return $this->filtered_inputs;
	}

	public function getFilteredOutputs()
	{
		return $this->filtered_outputs;
	}

	public function getEarningsSum()
	{
		return $this->addresses->getEarningsSum();
	}

	public function getSpendingsSum()
	{
		return $this->addresses->getSpendingsSum();
	}

	public function getFeesSum()
	{
		return $this->transactions->getFeesSum();
	}

	public function getInputsSum()
	{
		return $this->transactions->getInputsSum();
	}

	public function getOutputsSum()
	{
		return $this->transactions->getOutputsSum();
	}
}
