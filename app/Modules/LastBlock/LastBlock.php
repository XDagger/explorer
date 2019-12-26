<?php
namespace App\Modules\LastBlock;

use Illuminate\Database\Eloquent\Model;

class LastBlock extends Model
{
	const LIMIT = 20;

	/**
	 * The table associated with the model.
	 *
	 * @var string
	 */
	protected $table = 'last_blocks';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'address',
		'remark',
		'found_at',
	];

	/**
	 * The attributes that should be mutated to dates.
	 *
	 * @var array
	 */
	protected $dates = [
		'found_at',
	];

	/**
	 * @param $query
	 *
	 * @return mixed
	 */
	public function scopeWithoutBlockTimestamp($query)
	{
		return $query->whereNull('found_at');
	}

	/**
	 * @param $query
	 *
	 * @return mixed
	 */
	public function scopeLimited($query)
	{
		return $query->limit(self::LIMIT);
	}

	/* getters */
	public function getRemarkColorAttribute()
	{
		$hash = crc32($this->remark);

		$hue = $hash % 215;
		$saturation = [50, 55, 60][$hash / 360 % 3];
		$lightness = [50, 55, 60][$hash / 360 / 3 % 3];

		return "hsl({$hue}, {$saturation}%, {$lightness}%)";
	}
}
