<?php namespace App\Xdag\Network;

use Illuminate\Database\Eloquent\Model;

class Stat extends Model
{
	protected $table = 'stats';
	public $timestamps = false;
	protected $dates = ['created_at'];
	protected $guarded = [];
}
