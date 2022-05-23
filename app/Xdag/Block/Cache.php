<?php namespace App\Xdag\Block;

use App\Xdag\Node;
use App\Xdag\Exceptions\XdagException;
use JsonMachine\Items;

class Cache
{
	public static function getBlock(string $id): Block
	{
		if (!preg_match('/^([a-zA-Z0-9\/+]{32}|[a-f0-9]{64}|[0-9]{1,10})$/su', $id))
			throw new \InvalidArgumentException('Incorrect address, block hash or height.');

		try {
			$block = Block::create([
				'id' => $id,
				'expires_at' => now()->addMinutes(3),
			]);
		} catch (\Illuminate\Database\QueryException $ex) {
			//return self::waitForBlock($id);
		}

		$blockData = Items::fromStream(Node::streamRpc('xdag_getBlockByHash', [$id]), ['pointer' => ['/result/height', '/result/addresses', '/result/txLinks']]);

		foreach ($blockData as $key => $value) {
			dump($key, $value);
		}

		dd('x');

		return $block;
	}

	protected static function waitForBlock(string $id): Block
	{
		do {
			$block = Block::findOrFail($id);

			if ($block->cacheReady())
				return $block;
			else
				sleep(3);
		} while (true);
	}
}