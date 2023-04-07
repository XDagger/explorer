<?php namespace App\Xdag\Block;

use Illuminate\Database\Eloquent\Model;

class MainBlock extends Model
{
	protected $table = 'main_blocks';
	protected $primaryKey = 'address';
	public $incrementing = false;
	public $timestamps = false;
	protected $casts = ['created_at' => 'datetime'];
	protected $dateFormat = 'Y-m-d H:i:s.v';
	protected $guarded = [];
}
