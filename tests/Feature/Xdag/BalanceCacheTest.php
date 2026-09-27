<?php

declare(strict_types=1);

use App\Xdag\Block\Balance;
use App\Xdag\Block\Cache;
use App\Xdag\Exceptions\XdagException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    config(['xdag.rpc_url' => 'http://node.test:10001']);
});

function cachedBalance(string $id): Balance
{
    test()->trackBalance($id);

    return Cache::getBalance($id);
}

it('rejects ids that are not an address, a hash or a height', function () {
    Http::fake();

    expect(fn () => Cache::getBalance('not an address'))->toThrow(InvalidArgumentException::class, 'Incorrect address, block hash or main block height.');

    Http::assertNothingSent();
});

it('fetches the balance of an address from the node', function (Closure $id, string $method) {
    $id = $id();

    fakeNode([$method => '1234.567890123']);

    $balance = cachedBalance($id);

    expect($balance->blockExists())->toBeTrue()
        ->and($balance->balance)->toBe('1234.567890123')
        ->and(Balance::query()->findOrFail($id))
        ->state->toBe('found')
        ->balance->toBe('1234.567890123');

    Http::assertSent(fn (Request $request) => $request['method'] === $method && $request['params'] === [$id]);
})->with([
    'wallet address' => [fn () => walletAddress(), 'xdag_getBalance'],
    'block address' => [fn () => blockAddress(), 'xdag_getBalance'],
    'hash' => [fn () => blockHash(), 'xdag_getBalance'],
    'height' => [fn () => (string) random_int(1, 9_999_999_999), 'xdag_getBalanceByNumber'],
]);

it('remembers addresses the node does not know', function (string $response) {
    $id = walletAddress();

    fakeNode(['xdag_getBalance' => $response()]);

    expect(cachedBalance($id))
        ->blockExists()->toBeFalse()
        ->and(Balance::query()->findOrFail($id)->state)->toBe('not found');
})->with([
    'result omitted' => 'rpcWithoutResult',
    'null result' => 'rpcNullResult',
    'node error' => 'rpcError',
]);

it('takes the balance of an already cached block without asking the node', function () {
    Http::fake();

    $block = $this->createBlock(['balance' => '77.000000001']);

    expect(Cache::getBalance($block->id))
        ->blockExists()->toBeTrue()
        ->balance->toBe('77.000000001');

    Http::assertNothingSent();
});

it('serves a cached balance without asking the node again', function () {
    $id = walletAddress();

    fakeNode(['xdag_getBalance' => '5.000000000']);

    cachedBalance($id);

    expect(Cache::getBalance($id)->balance)->toBe('5.000000000');

    Http::assertSentCount(1);
});

it('forgets the balance when the node cannot be asked', function (Closure $fake, string $exception) {
    $id = walletAddress();

    $fake();

    expect(fn () => cachedBalance($id))->toThrow($exception)
        ->and(Balance::query()->find($id))->toBeNull();
})->with([
    'unreachable node' => [fn () => Http::fake(fn () => Http::failedConnection()), XdagException::class],
    'no RPC URL configured' => [fn () => config(['xdag.rpc_url' => '']), RuntimeException::class],
]);

it('refuses to report the existence of a balance it has not fetched yet', function () {
    (new Balance(['state' => null]))->blockExists();
})->throws(XdagException::class, 'Balance cache is not filled.');
