<?php namespace App\Xdag\Block;

use App\Xdag\Node;
use App\Xdag\Exceptions\XdagException;
use Illuminate\Database\QueryException;
use JsonMachine\Items;
use JsonMachine\JsonDecoder\ExtJsonDecoder;

class Cache
{
	const TTL = 3 * 60;

	public static function getBlock(string $id): Block
	{
		if (strlen($id) < 32 && !ctype_digit($id))
			$id = str_pad($id, 32, '/');

		if (!preg_match('/^([a-zA-Z0-9\/+]{32}|[a-f0-9]{64}|[0-9]{1,10})$/u', $id))
			throw new \InvalidArgumentException('Incorrect address, block hash or main block height.');

		try {
			$block = Block::create([
				'id' => $id,
				'expires_at' => now()->addSeconds(self::TTL),
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
			'address' => 'address',
			'remark' => 'remark',
			'diff' => 'difficulty',
			'type' => 'type',
			'timeStamp' => 'timestamp',
			'flags' => 'flags',
		];

		$blockData = Items::fromStream(Node::streamRpc(strlen($id) < 32 ? 'xdag_getBlockByNumber' : 'xdag_getBlockByHash', [$id]), [
			'pointer' => collect(array_keys($fields))->merge(['refs', 'transactions'])->map(fn($i) => "/result/$i")->toArray(),
			'decoder' => new ExtJsonDecoder(true),
		]);

		$basicBlockDataSaved = false;
		$blockState = null;

		try {
			foreach ($blockData as $key => $value) {
				// basic block data
				if ($key === 'state') {
					$blockState = $value;
					continue;
				}

				if (!is_array($value)) {
					if ($key === 'blockTime')
						$block->{$fields[$key]} = timestampToCarbon($value);
					else if ($key === 'diff')
						$block->{$fields[$key]} = substr($value, 2);
					else if ($key === 'timeStamp')
						$block->{$fields[$key]} = dechex($value);
					else
						$block->{$fields[$key]} = $value;

					continue;
				}

				if (!$basicBlockDataSaved) {
					$block->save();
					$basicBlockDataSaved = true;
				}

				if (str_ends_with($blockData->getCurrentJsonPointer(), 'refs')) { // "block as transaction"
					$block->transactions()->create([
						'ordering' => $key,
						'view' => 'transaction',
						'direction' => $direction = ['input', 'output', 'fee'][$value['direction']],
						'address' => $value['address'],
						'amount' => $value['amount'] * ($direction === 'input' ? 1 : -1),
					]);
				} else { // "block as address"
					$block->transactions()->create([
						'ordering' => $key,
						'view' => 'wallet',
						'direction' => $direction = ['input', 'output', 'earning'][$value['direction']],
						'address' => $value['address'],
						'amount' => $value['amount'] * ($direction === 'output' ? -1 : 1),
						'remark' => $value['remark'],
						'created_at' => timestampToCarbon($value['time']),
					]);
				}
			}
		} catch (\JsonMachine\Exception\PathNotFoundException $ex) {
			// block does not exist, thrown when *any* of the paths is not found in json stream
			$block->state = 'not found';
			$block->expires_at = now()->addSeconds(self::TTL);
			$block->save();

			return $block;
		} catch (\Throwable $ex) {
			// error while processing block data
			$block->delete();

			throw $ex;
		}

		$block->state = $blockState;
		$block->save();

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

	public static function getBalance(string $id): Balance
	{
		if (strlen($id) < 32 && !ctype_digit($id))
			$id = str_pad($id, 32, '/');

		if (!preg_match('/^([a-zA-Z0-9\/+]{32}|[a-f0-9]{64}|[0-9]{1,10})$/u', $id))
			throw new \InvalidArgumentException('Incorrect address, block hash or main block height.');

		try {
			$balance = Balance::create([
				'id' => $id,
				'expires_at' => now()->addSeconds(self::TTL),
			]);

			$json = Node::callRpc(strlen($id) < 32 ? 'xdag_getBalanceByNumber' : 'xdag_getBalance', [$id]);
		} catch (QueryException $ex) {
			return self::waitForBalance($id);
		} catch (XdagException $ex) {
			// error while fetching balance
			$balance->delete();

			throw $ex;
		}

		if (!isset($json['result'])) {
			// block does not exist
			$balance->state = 'not found';
			$balance->save();
			return $balance;
		}

		$balance->state = 'found';
		$balance->balance = $json['result'];
		$balance->save();

		return $balance;
	}

	protected static function waitForBalance(string $id): Balance
	{
		do {
			$balance = Balance::findOrFail($id);

			if ($balance->cacheReady())
				return $balance;
			else
				sleep(3);
		} while (true);
	}
}