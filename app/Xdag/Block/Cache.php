<?php namespace App\Xdag\Block;

use App\Xdag\Node;
use App\Xdag\Exceptions\XdagException;
use Illuminate\Database\QueryException;

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

		try {
			$stream = Node::streamRpc(strlen($id) < 32 ? 'xdag_getBlockByNumber' : 'xdag_getBlockByHash', [$id]);

			// read until we get all base block data
			$buffer = stream_get_line($stream, 512, ',"refs":');

			if (strpos($buffer, '"result":null') !== false) {
				$block->state = 'not found';
				$block->expires_at = now()->addSeconds(self::TTL);
				$block->save();

				return $block;
			}

			// construct base block data valid json and decode it
			$baseBlockData = json_decode("$buffer}}", true, 16, JSON_THROW_ON_ERROR)['result'];

			$block->fill([
				'height' => $baseBlockData['height'],
				'balance' => $baseBlockData['balance'],
				'created_at' => timestampToCarbon($baseBlockData['blockTime']),
				'state' => $baseBlockData['type'] === 'Snapshot' ? $baseBlockData['type'] : $baseBlockData['state'],
				'hash' => $baseBlockData['hash'],
				'address' => $baseBlockData['address'],
				'remark' => $baseBlockData['remark'] === '' ? null : $baseBlockData['remark'],
				'difficulty' => $baseBlockData['diff'] !== null ? substr($baseBlockData['diff'], 2) : null,
				'type' => $baseBlockData['type'],
				'timestamp' => dechex($baseBlockData['timeStamp']),
				'flags' => $baseBlockData['flags'],
			]);

			unset($baseBlockData);

			//  there are maximally 12 "refs" in a block, read all into memory and decode
			$refs = json_decode(stream_get_line($stream, 2560, ',"transactions":'), true, JSON_THROW_ON_ERROR);

			if (is_array($refs)) {
				foreach ($refs as $key => $ref) {
					$block->transactions()->create([
						'ordering' => $key,
						'view' => 'transaction',
						'direction' => $direction = ['input', 'output', 'fee'][$ref['direction']],
						'address' => $ref['address'],
						'amount' => $ref['amount'] * ($direction === 'input' ? 1 : -1),
					]);
				}
			}

			unset($refs);

			// read every transaction one by one
			// we have to split by direction json part as remark can contain \, " and } characters so we can't easily read until "}
			$i = 0;
			while (($buffer = stream_get_line($stream, 512, '{"direction":')) !== false) {
				if (strlen($buffer) < 96) // address and hashlow are 96 bytes, plus any json markup, so 96 is a safe minimal buffer length value
					continue;

				// strip leftover json markup
				$buffer = rtrim($buffer, ",]}\n\r");

				// decode and insert the transaction
				$transaction = json_decode("{\"direction\":$buffer}", true, 16, JSON_THROW_ON_ERROR);

				$block->transactions()->create([
					'ordering' => $i++,
					'view' => 'wallet',
					'direction' => $direction = ['input', 'output', 'earning', 'snapshot'][$transaction['direction']],
					'address' => $transaction['address'],
					'amount' => $transaction['amount'] * ($direction === 'output' ? -1 : 1),
					'remark' => $transaction['remark'] === '' ? null : $transaction['remark'],
					'created_at' => timestampToCarbon($transaction['time']),
				]);
			}
		} catch (\Throwable $ex) {
			// error while processing block data
			$block->transactions()->delete();
			$block->delete();

			throw $ex;
		}

		// save late to persist block details as last operation (marks cache fill is complete)
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

		// if we already have a block stored with this id, return it's balance
		if ($block = Block::whereId($id)->whereNotNull('balance')->first())
			return new Balance([
				'state' => 'found',
				'balance' => $block->balance
			]);

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
