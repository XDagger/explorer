<?php
namespace App\Modules\Network;

use Illuminate\Database\Eloquent\Model;

class Network extends Model
{
	const DAYS_LIMIT = 3;

	protected $table = 'network';

	protected $fillable = ['blocks', 'main_blocks', 'difficulty', 'supply', 'hashrate', 'created_at'];

	protected $casts = ['blocks' => 'int', 'main_blocks' => 'int', 'supply' => 'int', 'hashrate' => 'float'];
}
