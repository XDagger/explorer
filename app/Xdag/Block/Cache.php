<?php namespace App\Xdag\Block;

use App\Xdag\Node;
use JsonMachine\Items;
use JsonMachine\JsonDecoder\ExtJsonDecoder;

class Cache
{
	const TTL = 3 * 60;

	public static function getBlock(string $id): Block
	{
		if (strlen($id) < 32 && !ctype_digit($id))
			$id = str_pad($id, 32, '/');

		if (!preg_match('/^([a-zA-Z0-9\/+]{32}|[a-f0-9]{64}|[0-9]{1,10})$/su', $id))
			throw new \InvalidArgumentException('Incorrect address, block hash or height.');

		try {
			$block = Block::create([
				'id' => $id,
				'expires_at' => now()->addSeconds(self::TTL),
				'address' => $id, // FIXME: when searching by hash or number this is wrong, temporary in place as we can't iterate over 'address' and 'addresses' at the same time using json pointers
			]);
		} catch (\Illuminate\Database\QueryException $ex) {
			return self::waitForBlock($id);
		}

		// node RPC => our DB field name
		$fields = [
			'height' => 'height',
			'balance' => 'balance',
			'blockTime' => 'created_at',
			'state' => 'state',
			'hash' => 'hash',
			'remark' => 'remark',
			'diff' => 'difficulty',
			'type' => 'type',
		];

		$blockData = Items::fromStream(Node::streamRpc(strlen($id) < 32 ? 'xdag_getBlockByNumber' : 'xdag_getBlockByHash', [$id]), [
			'pointer' => collect(array_keys($fields))->merge(['addresses', 'txLinks'])->map(fn($i) => "/result/$i")->toArray(), // FIXME: we can't iterate 'address' because the json pointers overlap, update after node code change
			'decoder' => new ExtJsonDecoder(true),
		]);

		$basicBlockDataSaved = false;

		try {
			foreach ($blockData as $key => $value) {
				// basic block data
				if (!is_array($value)) {
					$block->{$fields[$key]} = $key === 'blockTime' ? timestampToCarbon($value) : $value;
					continue;
				}

				if (!$basicBlockDataSaved) {
					$block->save();
					$basicBlockDataSaved = true;
				}

				if (str_ends_with($blockData->getCurrentJsonPointer(), 'addresses')) { // "block as transaction" listing for now (update after node code change)
					$block->transactions()->create([
						'ordering' => $key,
						'view' => 'transaction',
						'direction' => ['input', 'output', 'fee'][$value['direction']],
						'address' => $value['address'],
						'amount' => $value['amount'],
					]);
				} else { // "block as address" listing for now (update after node code change)
					$block->transactions()->create([
						'ordering' => $key,
						'view' => 'wallet',
						'direction' => ['input', 'output', 'earning'][$value['direction']],
						'address' => $value['address'],
						'amount' => $value['amount'],
						'remark' => $value['remark'],
						'created_at' => timestampToCarbon($value['time']),
					]);
				}
			}
		} catch (\JsonMachine\Exception\PathNotFoundException $ex) {
			// block does not exist
			$block->state = 'not found';
			$block->expires_at = now()->addSeconds(self::TTL);
			$block->save();

			return $block;
		} catch (\Throwable $ex) {
			// error while processing block data
			$block->delete();

			throw $ex;
		}

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