<?php namespace App\Xdag\Block;

use Illuminate\Database\Eloquent\Model;

class Balance extends Model
{
	protected $table = 'balances';
	public $incrementing = false;
	protected $keyType = 'string';
	public $timestamps = false;
	protected $casts = ['expires_at' => 'datetime'];
	protected $dateFormat = 'Y-m-d H:i:s.v';
	protected $guarded = [];

	/* methods */
	public function cacheReady(): bool
	{
		return $this->state !== null;
	}

	public function blockExists(): bool
	{
		return $this->ensureCacheReady() || $this->state !== 'not found';
	}

	protected function ensureCacheReady(): void
	{
		if (!$this->cacheReady())
			throw new \App\Xdag\XdagException('Balance cache is not filled.');
	}
}
