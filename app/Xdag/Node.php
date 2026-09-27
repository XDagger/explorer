<?php

declare(strict_types=1);

namespace App\Xdag;

use App\Xdag\Exceptions\XdagException;
use GuzzleHttp\Psr7\StreamWrapper;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class Node
{
    public static function callRpc(string $method, array $parameters = []): array
    {
        $callId = random_int(1, 10000000);
        $response = (string) self::send($method, $parameters, $callId, 5)?->body();

        $json = json_decode($response, true);

        if (! is_array($json) || ($json['jsonrpc'] ?? '') !== '2.0' || ($json['id'] ?? '') !== $callId) {
            throw new XdagException("RPC method '$method' returned unexpected response: '$response'");
        }

        return $json;
    }

    /**
     * @return resource
     */
    public static function streamRpc(string $method, array $parameters = [])
    {
        $response = self::send($method, $parameters, random_int(1, 10000000), 60, stream: true);

        if ($response === null) {
            throw new XdagException("RPC method '$method' could not reach the node.");
        }

        return StreamWrapper::getResource($response->toPsrResponse()->getBody());
    }

    public static function rpcUrl(): string
    {
        $url = (string) config('xdag.rpc_url');

        if ($url === '') {
            throw new RuntimeException('XDAG node RPC URL is not set.');
        }

        return $url;
    }

    protected static function send(string $method, array $parameters, int $callId, int $timeout, bool $stream = false): ?Response
    {
        try {
            return self::request($timeout, $stream)->post(self::rpcUrl(), [
                'jsonrpc' => '2.0',
                'method' => $method,
                'params' => $parameters,
                'id' => $callId,
            ]);
        } catch (ConnectionException) {
            return null;
        }
    }

    protected static function request(int $timeout, bool $stream): PendingRequest
    {
        return Http::asJson()
            ->withHeaders(['Connection' => 'close'])
            ->withoutRedirecting()
            ->timeout($timeout)
            ->withOptions(['stream' => $stream]);
    }
}
