<?php namespace App\Xdag\Block;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
	protected $table = 'block_transactions';
	public $incrementing = false;
	public $primaryKey = null;
	public $timestamps = false;
	protected $dates = ['created_at'];

	/* scopes */
	public function scopeAsAddress($q)
	{
		return $q->whereView('address');
	}

	public function scopeAsTransaction($q)
	{
		return $q->whereView('transaction');
	}
}
