<?php namespace App\Xdag\Block;

use Illuminate\Database\Eloquent\Model;

class Block extends Model
{
	protected $table = 'blocks';
	public $incrementing = false;
	protected $keyType = 'string';
	public $timestamps = false;
	protected $dates = ['created_at', 'expires_at'];

	/* relations */
	public function transactions()
	{
		return $this->hasMany(Transaction::class)->orderBy('ordering');
	}
}
