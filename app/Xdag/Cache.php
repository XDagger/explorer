<?php

namespace App\Xdag;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Xdag\Exceptions\XdagException;

class Cache extends Model
{
	protected $table = 'cache';

	public $incrementing = false;

	protected $primaryKey = 'key';
	protected $keyType = 'string';

	protected $fillable = ['key', 'file', 'expires_at'];

	protected $dates = ['expires_at', 'created_at', 'updated_at'];

	public static function read(string $key, callable $read_callback)
	{
		$cache = self::find($key);

		if (!$cache)
			return false;

		$tries = 60;
		while ($tries && $cache->file === null) {
			sleep(1);

			$cache = self::find($key);

			if (!$cache)
				return false;

			$tries--;
		}

		if ($cache->file === null)
			return false;

		$file = @fopen(storage_path('cache') . '/' . $cache->file, 'rb');

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
			$file_name = Str::random(32);
			$file = @fopen(storage_path('cache') . '/' . $file_name, 'x');
			$tries--;
		} while (!$file && $tries > 0);

		if (!$file)
			return false;

		try {
			if (call_user_func($write_callback, $file) === false)
				throw new \Exception;
		} catch (\Exception $ex) {
			// callback was unable to write the cache, delete entry
			$cache->delete();
			fclose($file);
			return false;
		}

		$cache->file = $file_name;
		fclose($file);

		try {
			$cache->save();
		} catch (\Exception $ex) {
			return false;
		}

		return true;
	}

	public static function copy(string $key, string $other_key)
	{
		$cache = self::find($key);

		if (!$cache || $cache->file === null)
			return false;

		$cache = new self([
			'key' => $other_key,
			'expires_at' => $cache->expires_at,
			'file' => $cache->file,
		]);

		try {
			$cache->save();
		} catch (\Exception $ex) {
			return false;
		}

		return true;
	}
}
