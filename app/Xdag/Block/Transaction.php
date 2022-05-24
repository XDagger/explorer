<?php namespace App\Xdag\Block;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
	protected $table = 'block_transactions';
	public $incrementing = false;
	public $primaryKey = null;
	public $timestamps = false;
	protected $dates = ['created_at'];
	protected $dateFormat = 'Y-m-d H:i:s.v';
	protected $guarded = [];

	/* scopes */
	public function scopeWallet($q)
	{
		return $q->whereView('wallet');
	}

	public function scopeTransaction($q)
	{
		return $q->whereView('transaction');
	}

	public function scopeEarnings($q)
	{
		return $q->whereIn('direction', ['input', 'earning']);
	}

	public function scopeSpendings($q)
	{
		return $q->whereIn('direction', ['output', 'fee']);
	}
}
