<?php namespace App\Xdag\Block;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
	protected $table = 'block_transactions';
	public $timestamps = false;
	protected $casts = ['created_at' => 'datetime'];
	protected $dateFormat = 'Y-m-d H:i:s.v';
	protected $guarded = [];

	/* scopes */
	public function scopeWallet($q)
	{
		return $q->whereView('wallet')->orderBy('created_at', 'desc')->orderBy('id');
	}

	public function scopeTransaction($q)
	{
		return $q->whereView('transaction')->orderBy('id');
	}

	public function scopeEarnings($q)
	{
		return $q->whereIn('direction', ['input', 'earning', 'snapshot']);
	}

	public function scopeSpendings($q)
	{
		return $q->whereIn('direction', ['output', 'fee']);
	}
}
