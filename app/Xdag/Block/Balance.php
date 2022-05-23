<?php namespace App\Xdag\Block;

use Illuminate\Database\Eloquent\Model;

class Block extends Model
{
	protected $table = 'balances';
	public $incrementing = false;
	protected $keyType = 'string';
	public $timestamps = false;
	protected $dates = ['expires_at'];
}
