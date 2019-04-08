<?php

namespace App\Xdag;

use Illuminate\Database\Eloquent\Model;
use App\Xdag\Exceptions\XdagException;

class Cache extends Model
{
	protected $table = 'cache';
	public $incrementing = false;
	protected $primaryKey = 'key';
	protected $keyType = 'string';

	protected $fillable = ['key', 'file', 'expires_at'];

	protected $dates = ['expires_at', 'created_at', 'updated_at'];

	public function hasKey($key)
	{
		return self::where('key', $key)->exists();
	}

	public static function read(string $key, callable $read_callback)
	{
		$result = self::find($key);

		if (!$result)
			return false;

		$tries = 60;
		while ($tries && $result->file === null) {
			sleep(1);

			$result = self::find($key);

			if (!$result)
				return false;

			$tries--;
		}

		if ($result->file === null)
			return false;

		$file = @fopen(storage_path('cache') . '/' . $result->file, 'rb');

		if (!$file)
			return false;

		call_user_func($read_callback, $file);
		fclose($file);

		return true;
	}

	public static function write(string $key, int $ttl, callable $write_callback)
	{
		$expires_at = now()->addMinutes($ttl);

		$cache = new self([
			'key' => $key,
			'expires_at' => $expires_at,
		]);

		try {
			$cache->save();
		} catch (\Exception $ex) {
			return false;
		}

		$tries = 5;

		do {
			$file_name = str_random(32);
			$file = @fopen(storage_path('cache') . '/' . $file_name, 'x');
			$tries--;
		} while (!$file && $tries > 0);

		if (!$file)
			return false;

		call_user_func($write_callback, $file);
		fclose($file);

		$cache->file = $file_name;

		try {
			$cache->save();
		} catch (\Exception $ex) {
			return false;
		}

		return true;
	}
}
