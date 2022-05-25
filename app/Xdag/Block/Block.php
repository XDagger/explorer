<?php namespace App\Xdag\Block;

use Illuminate\Database\Eloquent\Model;

class Block extends Model
{
	protected $table = 'blocks';
	public $incrementing = false;
	protected $keyType = 'string';
	public $timestamps = false;
	protected $dates = ['created_at', 'expires_at'];
	protected $dateFormat = 'Y-m-d H:i:s.v';
	protected $guarded = [];

	/* relations */
	public function transactions()
	{
		return $this->hasMany(Transaction::class)->orderBy('ordering', 'desc'); // newest transactions first
	}

	/* methods */
	public function cacheReady(): bool
	{
		return $this->state !== null;
	}

	public function existsOnBlockchain(): bool
	{
		return $this->ensureCacheReady() || $this->state !== 'not found';
	}

	public function isMainBlock(): bool
	{
		return $this->ensureCacheReady() || $this->type === 'Main';
	}

	public function isTransactionBlock(): bool
	{
		return $this->ensureCacheReady() || $this->type === 'Transaction';
	}

	public function walletGraph(string $type): array
	{
		$builder = $this->transactions()->wallet()
			->groupByRaw('DATE_FORMAT(created_at, "%Y-%m-%d")')
			->reorder()
			->orderByRaw('DATE_FORMAT(created_at, "%Y-%m-%d")')
		;

		if ($type === 'earnings' || $type === 'spendings') {
			$builder->selectRaw('ABS(SUM(amount)) amount, DATE_FORMAT(created_at, "%Y-%m-%d") date');
			$builder->$type();
		} else {
			$builder->selectRaw('SUM(amount) amount, DATE_FORMAT(created_at, "%Y-%m-%d") date');
		}

		$data = $builder->get();
		unset($builder);

		$graph = array_combine($data->pluck('date')->toArray(), $data->pluck('amount')->toArray());

		$now = now();
		$date = (clone $now)->subDays(6);

		do {
			if (!isset($graph[$date->format('Y-m-d')]))
				$graph[$date->format('Y-m-d')] = '0.000000000';

			$date->addDays(1);
		} while ($date <= $now);

		ksort($graph);

		$labels = array_keys($graph);
		$values = array_values($graph);

		if ($type === 'balance') {
			foreach ($values as $index => &$value) {
				if (!$index)
					continue;

				$value = bcadd($value, $values[$index - 1], 9);
			}
		}

		return [
			'labels' => $labels,
			'values' => $values,
		];
	}

	protected function ensureCacheReady(): void
	{
		if (!$this->cacheReady())
			throw new \App\Xdag\XdagException('Block cache is not filled.');
	}
}
