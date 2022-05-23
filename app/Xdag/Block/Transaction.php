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
	public function scopeBlockAsWallet($q)
	{
		return $q->whereView('wallet');
	}

	public function scopeBlockAsTransaction($q)
	{
		return $q->whereView('transaction');
	}
}
