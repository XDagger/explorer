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
	public function getRemarkBackgroundColorAttribute()
	{
		$hash = crc32($this->remark);

		$hue = 31 + $hash % 247;
		$lightness = 80 + ($hash % 3 * 5);

		return "hsl({$hue}, 50%, {$lightness}%)";
	}

	public function getFirstRemarkLinkAttribute()
	{
		if (preg_match('~https?://\S+~siu', $this->remark, $match))
			return $match[0];
	}

	public function getCleanRemarkAttribute()
	{
		return preg_replace('~https?://([a-z0-9-.]+)\S*~siu', '$1', e($this->remark));
	}

	// used in text view
	public function getTextRemarkAttribute()
	{
		return preg_replace('~https?://([a-z0-9-.]+)\S*~siu', '<a href="$0" target="_blank" style="color: inherit">$1</a>', e($this->remark));
	}
}
