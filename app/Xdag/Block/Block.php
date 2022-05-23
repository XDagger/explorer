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

	protected function ensureCacheReady(): void
	{
		if (!$this->cacheReady())
			throw new \App\Xdag\XdagException('Block cache is not filled.');
	}
}
