<?php

declare(strict_types=1);

use App\Xdag\Block\Block;
use App\Xdag\Block\Cache;
use App\Xdag\Exceptions\XdagException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    config(['xdag.rpc_url' => 'http://node.test:10001', 'explorer.getblock_binary_name' => '']);
});

function cachedBlock(string $id): Block
{
    test()->trackBlock($id);

    return Cache::getBlock($id);
}

/**
 * @return array<int, array<string, mixed>>
 */
function storedTransactions(string $id, string $view): array
{
    return Block::query()->findOrFail($id)->transactions()
        ->where('view', $view)
        ->orderBy('id')
        ->get(['direction', 'address', 'amount', 'remark', 'created_at'])
        ->map(fn ($transaction) => [
            'direction' => $transaction->direction,
            'address' => $transaction->address,
            'amount' => $transaction->amount,
            'remark' => $transaction->remark,
            'created_at' => $transaction->created_at?->format('Y-m-d H:i:s.v'),
        ])
        ->all();
}

it('rejects ids that are not an address, a hash or a height', function (string $id) {
    Http::fake();

    expect(fn () => Cache::getBlock($id))->toThrow(InvalidArgumentException::class, 'Incorrect address, block hash or main block height.');

    Http::assertNothingSent();
})->with([
    'empty' => '',
    'too long height' => '12345678901',
    'short address' => 'abc',
    'uppercase hash' => str_repeat('A', 64),
    'path traversal' => '../../etc/passwd',
    'base58 with zero' => '0'.str_repeat('a', 30),
]);

it('stores the details of a block found by its address', function () {
    $block = nodeBlock([
        'height' => 3_123_456,
        'balance' => '1024.123456789',
        'blockTime' => 1790000000123,
        'state' => 'Main',
        'remark' => 'pool.example.com',
        'diff' => '0x00000000000000000000123abc',
        'type' => 'Main',
        'flags' => '3f',
    ]);

    fakeNode(['xdag_getBlockByHash' => $block]);

    $stored = cachedBlock($block['address']);

    expect($stored->fresh())
        ->cacheReady()->toBeTrue()
        ->existsOnBlockchain()->toBeTrue()
        ->isMainBlock()->toBeTrue()
        ->height->toBe(3_123_456)
        ->balance->toBe('1024.123456789')
        ->state->toBe('Main')
        ->hash->toBe($block['hash'])
        ->address->toBe($block['address'])
        ->remark->toBe('pool.example.com')
        ->difficulty->toBe('00000000000000000000123abc')
        ->type->toBe('Main')
        ->timestamp->toBe(dechex($block['timeStamp']))
        ->flags->toBe('3f')
        ->and($stored->fresh()->created_at->format('Y-m-d H:i:s.v e'))->toBe('2026-09-21 14:13:20.123 UTC')
        ->and($stored->fresh()->expires_at->isFuture())->toBeTrue();

    Http::assertSent(fn (Request $request) => $request['method'] === 'xdag_getBlockByHash' && $request['params'] === [$block['address'], 1, 1000000000]);
});

it('looks blocks up by hash, wallet address and main block height', function (Closure $id, string $method) {
    $id = $id();
    fakeNode([$method => nodeBlock()]);

    cachedBlock($id);

    Http::assertSent(fn (Request $request) => $request['method'] === $method && $request['params'][0] === $id);
})->with([
    'hash' => [fn () => blockHash(), 'xdag_getBlockByHash'],
    'wallet address' => [fn () => walletAddress(), 'xdag_getBlockByHash'],
    'height' => [fn () => (string) random_int(1, 9_999_999_999), 'xdag_getBlockByNumber'],
]);

it('stores the inputs, outputs and fee of a transaction block', function () {
    $input = walletAddress();
    $output = walletAddress();
    $block = nodeBlock([
        'type' => 'Transaction',
        'balance' => '0.000000000',
        'refs' => [
            nodeRef(0, '100.000000000', $input),
            nodeRef(1, '99.900000000', $output),
            nodeRef(2, '0.100000000', $input),
        ],
    ]);

    fakeNode(['xdag_getBlockByHash' => $block]);

    expect(cachedBlock($block['hash'])->isTransactionBlock())->toBeTrue()
        ->and(storedTransactions($block['hash'], 'transaction'))->toBe([
            ['direction' => 'input', 'address' => $input, 'amount' => '100.000000000', 'remark' => null, 'created_at' => null],
            ['direction' => 'output', 'address' => $output, 'amount' => '-99.900000000', 'remark' => null, 'created_at' => null],
            ['direction' => 'fee', 'address' => $input, 'amount' => '-0.100000000', 'remark' => null, 'created_at' => null],
        ]);
});

it('stores the wallet history of an address', function () {
    $addresses = [blockAddress(), blockAddress(), blockAddress(), blockAddress()];
    $block = nodeBlock(['transactions' => [
        nodeTransaction(0, '5.000000001', 1790000000001, 'thanks', $addresses[0]),
        nodeTransaction(1, '2.500000000', 1790000001002, '', $addresses[1]),
        nodeTransaction(2, '64.000000000', 1790000002003, null, $addresses[2]),
        nodeTransaction(3, '1000.000000000', 1790000003004, '', $addresses[3]),
    ]]);

    fakeNode(['xdag_getBlockByHash' => $block]);

    cachedBlock($block['address']);

    expect(storedTransactions($block['address'], 'wallet'))->toBe([
        ['direction' => 'input', 'address' => $addresses[0], 'amount' => '5.000000001', 'remark' => 'thanks', 'created_at' => '2026-09-21 14:13:20.001'],
        ['direction' => 'output', 'address' => $addresses[1], 'amount' => '-2.500000000', 'remark' => null, 'created_at' => '2026-09-21 14:13:21.002'],
        ['direction' => 'earning', 'address' => $addresses[2], 'amount' => '64.000000000', 'remark' => null, 'created_at' => '2026-09-21 14:13:22.003'],
        ['direction' => 'snapshot', 'address' => $addresses[3], 'amount' => '1000.000000000', 'remark' => null, 'created_at' => '2026-09-21 14:13:23.004'],
    ]);
});

it('keeps remarks with characters that are special in JSON', function (string $remark) {
    $block = nodeBlock([
        'remark' => $remark,
        'transactions' => [nodeTransaction(0, '1.000000000', 1790000000000, $remark), nodeTransaction(0, '2.000000000', 1790000000000, $remark)],
    ]);

    fakeNode(['xdag_getBlockByHash' => $block]);

    expect(cachedBlock($block['address'])->fresh()->remark)->toBe($remark)
        ->and(array_column(storedTransactions($block['address'], 'wallet'), 'remark'))->toBe([$remark, $remark]);
})->with([
    'quotes and braces' => 'say "hi"} {x}',
    'closing brackets' => 'list]}],',
    'backslashes' => 'C:\\pool\\',
    'unicode' => 'Pool ✓ ž',
    'url' => 'https://pool.example.com/a?b=1',
]);

it('stores hundreds of wallet transactions streamed by the node', function () {
    $transactions = array_map(fn (int $i) => nodeTransaction($i % 3, "$i.000000000", 1790000000000 + $i), range(1, 500));
    $block = nodeBlock(['transactions' => $transactions]);

    fakeNode(['xdag_getBlockByHash' => $block]);

    cachedBlock($block['address']);

    expect(Block::query()->findOrFail($block['address'])->transactions()->count())->toBe(500);
});

it('stores a wallet address the node knows only by its balance', function () {
    $address = walletAddress();

    fakeNode(['xdag_getBlockByHash' => nodeBlock([
        'balance' => '7.000000000',
        'state' => 'Accepted',
        'hash' => null,
        'address' => $address,
        'remark' => null,
        'diff' => null,
        'type' => 'Wallet',
        'flags' => null,
        'refs' => null,
    ])]);

    expect(cachedBlock($address)->fresh())
        ->isWalletBlock()->toBeTrue()
        ->balance->toBe('7.000000000')
        ->hash->toBeNull()
        ->remark->toBeNull()
        ->difficulty->toBeNull()
        ->flags->toBeNull();
});

it('marks snapshot blocks with the snapshot state', function () {
    $block = nodeBlock(['type' => 'Snapshot', 'state' => null, 'diff' => null, 'flags' => null]);

    fakeNode(['xdag_getBlockByHash' => $block]);

    expect(cachedBlock($block['address'])->fresh())
        ->isSnapshotBlock()->toBeTrue()
        ->state->toBe('Snapshot');
});

it('remembers blocks the node does not know', function (string $response) {
    $id = blockHash();

    fakeNode(['xdag_getBlockByHash' => $response()]);

    $block = cachedBlock($id);

    expect($block->fresh())
        ->cacheReady()->toBeTrue()
        ->existsOnBlockchain()->toBeFalse()
        ->state->toBe('not found')
        ->and($block->transactions()->count())->toBe(0);
})->with([
    'result omitted, as by current nodes' => 'rpcWithoutResult',
    'null result, as by older nodes' => 'rpcNullResult',
]);

it('serves a cached block without asking the node again', function () {
    $block = nodeBlock(['balance' => '3.000000000']);

    fakeNode(['xdag_getBlockByHash' => $block]);

    cachedBlock($block['address']);

    expect(Cache::getBlock($block['address'])->balance)->toBe('3.000000000');

    Http::assertSentCount(1);
});

it('forgets the block when the node fails', function (Closure $fake, string $exception) {
    $id = blockHash();

    $fake();

    expect(fn () => cachedBlock($id))->toThrow($exception)
        ->and(Block::query()->find($id))->toBeNull();
})->with([
    'node error' => [fn () => fakeNode(['xdag_getBlockByHash' => rpcError()]), XdagException::class],
    'unreachable node' => [fn () => Http::fake(fn () => Http::failedConnection()), XdagException::class],
    'garbage response' => [fn () => fakeNode(['xdag_getBlockByHash' => fn () => '{"jsonrpc":"2.0","id":1,"result":{"height":1,"garbage']), JsonException::class],
]);

it('forgets the transactions of a block that failed half way', function () {
    $block = nodeBlock(['refs' => [nodeRef(0, '1.000000000')]]);
    $body = json_encode(['jsonrpc' => '2.0', 'id' => 1, 'result' => $block], JSON_UNESCAPED_SLASHES);
    $body = str_replace('"transactions":[]', '"transactions":[{"direction":0,"hashlow":"'.blockHash().'","address":"'.blockAddress().'","amount":"1.0","time":1,"remark":"unterminated', $body);

    fakeNode(['xdag_getBlockByHash' => fn () => $body]);

    expect(fn () => cachedBlock($block['address']))->toThrow(JsonException::class)
        ->and(Block::query()->find($block['address']))->toBeNull()
        ->and(DB::table('block_transactions')->where('block_id', $block['address'])->exists())->toBeFalse();
});
