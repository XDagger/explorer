<?php namespace App\Xdag\Network;

use Illuminate\Database\Eloquent\Model;

class Stat extends Model
{
	protected $table = 'stats';
	public $timestamps = false;
	protected $guarded = [];
	protected $casts = ['created_at' => 'datetime', 'connections' => 'array'];
}
