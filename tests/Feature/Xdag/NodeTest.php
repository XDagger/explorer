<?php

declare(strict_types=1);

use App\Xdag\Exceptions\XdagException;
use App\Xdag\Node;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    config(['xdag.rpc_url' => 'http://node.test:10001']);
});

it('calls a JSON-RPC 2.0 method of the node and returns the whole response', function () {
    fakeNode(['xdag_getBalance' => '12.500000000']);

    $response = Node::callRpc('xdag_getBalance', ['some-address']);

    expect($response)->toMatchArray(['jsonrpc' => '2.0', 'result' => '12.500000000']);

    Http::assertSent(fn (Request $request) => $request->url() === 'http://node.test:10001'
        && $request->method() === 'POST'
        && $request->isJson()
        && $request['jsonrpc'] === '2.0'
        && $request['method'] === 'xdag_getBalance'
        && $request['params'] === ['some-address']
        && is_int($request['id'])
        && $request->hasHeader('Connection', 'close'));
});

it('passes node errors to the caller as a response without a result', function () {
    fakeNode(['xdag_getBalance' => rpcError('Internal error: no such block')]);

    expect(Node::callRpc('xdag_getBalance', ['x']))
        ->not->toHaveKey('result')
        ->and(Node::callRpc('xdag_getBalance', ['x'])['error']['message'])->toBe('Internal error: no such block');
});

it('rejects responses to a different call', function () {
    fakeNode(['xdag_getStatus' => fn (array $params, int $id) => ['jsonrpc' => '2.0', 'id' => $id + 1, 'result' => []]]);

    Node::callRpc('xdag_getStatus');
})->throws(XdagException::class, "RPC method 'xdag_getStatus' returned unexpected response");

it('rejects responses that are not JSON-RPC 2.0', function (string $body) {
    fakeNode(['xdag_getStatus' => fn () => $body]);

    Node::callRpc('xdag_getStatus');
})->with([
    'html' => '<html>Bad gateway</html>',
    'empty' => '',
    'other protocol version' => '{"jsonrpc":"1.0","id":1,"result":[]}',
])->throws(XdagException::class);

it('reports an unreachable node', function () {
    Http::fake(fn () => Http::failedConnection('Connection refused'));

    Node::callRpc('xdag_getStatus');
})->throws(XdagException::class, "RPC method 'xdag_getStatus' returned unexpected response: ''");

it('refuses to call the node when no RPC URL is configured', function () {
    config(['xdag.rpc_url' => '']);

    Node::callRpc('xdag_getStatus');
})->throws(RuntimeException::class, 'XDAG node RPC URL is not set.');

it('streams the response of a method', function () {
    fakeNode(['xdag_getBlockByHash' => ['height' => 5]]);

    $stream = Node::streamRpc('xdag_getBlockByHash', ['hash', 1, 1000000000]);

    expect(is_resource($stream))->toBeTrue()
        ->and(json_decode(stream_get_contents($stream), true)['result'])->toBe(['height' => 5]);

    Http::assertSent(fn (Request $request) => $request['method'] === 'xdag_getBlockByHash' && $request['params'] === ['hash', 1, 1000000000]);
});

it('reports an unreachable node when streaming', function () {
    Http::fake(fn () => Http::failedConnection('Connection refused'));

    Node::streamRpc('xdag_getBlockByHash', ['hash', 1, 1000000000]);
})->throws(XdagException::class, "RPC method 'xdag_getBlockByHash' could not reach the node.");
