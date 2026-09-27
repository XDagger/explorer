<?php

declare(strict_types=1);

use Database\Factories\XdagIdentifier;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The suite runs in parallel against a shared database, so no test may rely on
| table state it did not create. Build what you need through the create*()
| methods of Tests\TestCase: they remove the rows again afterwards, and the
| ones for network stats and main blocks hold a lock that keeps tests of the
| network wide state from running at the same time.
|
*/

pest()->extend(TestCase::class)->in('Feature', 'Unit');

function blockHash(): string
{
    return XdagIdentifier::hash();
}

function blockAddress(): string
{
    return XdagIdentifier::address();
}

function walletAddress(): string
{
    return XdagIdentifier::walletAddress();
}

/*
|--------------------------------------------------------------------------
| XDAG node
|--------------------------------------------------------------------------
|
| The node answers JSON-RPC over HTTP. fakeNode() takes the result of each
| method the test expects to be called, or a closure that builds the whole
| response, and answers with the id of the request like the node does. Block
| responses keep the field order of XDAGj, as the block cache reads them as
| a stream in that order.
|
*/

/**
 * @param  array<string, mixed>  $methods
 */
function fakeNode(array $methods): void
{
    Http::fake(function (Request $request) use ($methods) {
        $method = $request['method'];

        if (! array_key_exists($method, $methods)) {
            throw new RuntimeException("Unexpected call of RPC method $method.");
        }

        $answer = $methods[$method];
        $response = $answer instanceof Closure
            ? $answer($request['params'], $request['id'])
            : ['jsonrpc' => '2.0', 'id' => $request['id'], 'result' => $answer];

        return Http::response(is_string($response) ? $response : json_encode($response, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    });
}

function rpcNullResult(): Closure
{
    return fn (array $params, int $id) => ['jsonrpc' => '2.0', 'id' => $id, 'result' => null];
}

function rpcWithoutResult(): Closure
{
    return fn (array $params, int $id) => ['jsonrpc' => '2.0', 'id' => $id];
}

function rpcError(string $message = 'Internal error: boom'): Closure
{
    return fn (array $params, int $id) => ['jsonrpc' => '2.0', 'id' => $id, 'error' => ['code' => -32603, 'message' => $message]];
}

/**
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function nodeBlock(array $overrides = []): array
{
    $hash = $overrides['hash'] ?? blockHash();
    $blockTime = $overrides['blockTime'] ?? 1790000000123;

    return array_merge([
        'height' => 0,
        'balance' => '10.500000000',
        'blockTime' => $blockTime,
        'timeStamp' => intdiv($blockTime * 1024, 1000),
        'state' => 'Accepted',
        'hash' => $hash,
        'address' => XdagIdentifier::addressOf($hash),
        'remark' => '',
        'diff' => '0x1a2b3c',
        'type' => 'Wallet',
        'flags' => '1c',
        'totalPage' => 1,
        'refs' => [],
        'transactions' => [],
    ], $overrides);
}

/**
 * @return array<string, mixed>
 */
function nodeRef(int $direction, string $amount, ?string $address = null): array
{
    return [
        'direction' => $direction,
        'address' => $address ?? walletAddress(),
        'hashlow' => blockHash(),
        'amount' => $amount,
    ];
}

/**
 * @return array<string, mixed>
 */
function nodeTransaction(int $direction, string $amount, int $time, ?string $remark = '', ?string $address = null): array
{
    return [
        'direction' => $direction,
        'hashlow' => blockHash(),
        'address' => $address ?? blockAddress(),
        'amount' => $amount,
        'time' => $time,
        'remark' => $remark,
    ];
}
