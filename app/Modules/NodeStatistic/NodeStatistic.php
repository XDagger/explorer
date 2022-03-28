<?php
namespace App\Modules\NodeStatistic;

use Illuminate\Database\Eloquent\Model;

class NodeStatistic extends Model
{
	/**
	 * The table associated with the model.
	 *
	 * @var string
	 */
	protected $table = 'node_statistics';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'node',
		'is_reachable',
		'reachable_at',
	];

	/**
	 * The attributes that should be cast to native types.
	 *
	 * @var array
	 */
	protected $casts = [
		'is_reachable' => 'bool',
	];

	/**
	 * The attributes that should be mutated to dates.
	 *
	 * @var array
	 */
	protected $dates = [
		'reachable_at'
	];

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany
	 */
	public function statistics()
	{
		return $this->hasMany(NodeStatistic::class, 'node', 'node')->oldest();
	}
}
