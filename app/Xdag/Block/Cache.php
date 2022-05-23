<?php namespace App\Xdag\Block;

use App\Xdag\Exceptions\XdagException;

class Cache
{
	static public function get(string $id): Block
	{
		if (!preg_match('/^([a-zA-Z0-9\/+]{32}|[a-f0-9]{64}|[0-9]{1,10})$/su', $id))
			throw new \InvalidArgumentException('Incorrect address, block hash or height.');

		try {
			$block = Block::create([
				'id' => $id,
				'expires_at' => now()->addMinutes(3),
			]);
		} catch (XdagException $ex) {
			//
		}
	}
}