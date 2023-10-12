<?php namespace App\Xdag\Block;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
	protected $table = 'block_notifications';
	public $incrementing = false;
	protected $keyType = 'string';
	public $timestamps = false;
	protected $casts = ['show_from' => 'datetime', 'show_to' => 'datetime'];
	protected $dateFormat = 'Y-m-d H:i:s';
	protected $guarded = [];

	/* relations */
	public function block()
	{
		return $this->belongsTo(Block::class, 'id');
	}

	/* scopes */
	public function scopeShown($query)
	{
		$now = now();
		return $query->where(fn($q) => $q->whereNull('show_from')->orWhere('show_from', '<=', $now))->where(fn($q) => $q->whereNull('show_to')->orWhere('show_to', '>=', $now));
	}
}
