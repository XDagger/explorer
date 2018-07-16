<?php
namespace App\Xdag\Block;

use App\Xdag\Block\Attributes\{Properties, Transactions, Addresses, Earnings, Spendings, Balances};

class Block
{
	const MAIN_BLOCK_FLAGS = '1f';
	const REWARD = 1024; // TODO: in future, reward may decrease

	protected $properties,  $transactions, $addresses;
	protected $earnings, $spendings, $balances;
	protected $earnings_change, $spendings_change, $balance_change;
	protected $total_earnings, $total_spendings;
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

		$this->total_earnings = $data['total_earnings'] ?? 0;
		$this->total_spendings = $data['total_spendings'] ?? 0;

		$this->total_transactions_count = $data['total_transactions_count'] ?? 0;
		$this->total_addresses_count = $data['total_addresses_count'] ?? 0;
	}

	public function isMainBlock()
	{
		return $this->properties->has('flags') && $this->properties->get('flags') === self::MAIN_BLOCK_FLAGS;
	}

	public function isTransactionBlock()
	{
		return !$this->isMainBlock() && $this->total_addresses_count == 0;
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
}
