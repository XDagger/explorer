<?php
namespace App\Xdag;

class Cache
{
	protected $storage_path;

	public function __construct()
	{
		$this->storage_path = storage_path('cache');
	}

	public function create($cmd, $ttl)
	{
		$path = $this->getPath($ttl);
		@mkdir($path);
		$file = @fopen($path . '/' . md5($cmd), 'x');

		if ($file === false)
			return false;

		fopen($path . '/lock_' . md5($cmd), 'x');

		return $file;
	}

	public function finishCreate($cmd, $ttl)
	{
		$path = $this->getPath($ttl);
		@unlink($path . '/lock_' . md5($cmd));
	}

	public function open($cmd, $ttl)
	{
		$path = $this->getPath($ttl);
		$lock = $path . '/lock_' . md5($cmd);
		$wait = $ttl * 60;

		while ($wait && file_exists($lock)) {
			sleep(1);
			$wait--;
		}

		$file = @fopen($this->getPath($ttl) . '/' . md5($cmd), 'r');

		if ($file === false)
			return false;

		return $file;
	}

	public function getPath($ttl)
	{
		return $this->storage_path . '/' . $ttl;
	}
}
