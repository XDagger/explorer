<?php namespace App\Xdag\Block;

use Illuminate\Database\Eloquent\Model;

class MainBlock extends Model
{
	protected $table = 'main_blocks';
	public $incrementing = false;
	public $timestamps = false;
	protected $dates = ['created_at'];
	protected $dateFormat = 'Y-m-d H:i:s.v';
	protected $guarded = [];
}
