<?php namespace App\Xdag;

use App\Xdag\Exceptions\XdagException;

class Node
{
	public static function callRpc(string $method, array $parameters = []): mixed
	{
		$response = @file_get_contents(self::rpcUrl(), false, self::streamContext($method, $parameters, $callId = rand(1, 10000000), 5));

		$json = @json_decode((string) $response, true);

		if (!is_array($json) || ($json['jsonrpc'] ?? '') !== '2.0' || ($json['id'] ?? '') !== $callId)
			throw new XdagException("RPC method '$method' returned unexpected response: '$response'");

		return $json;
	}

	public static function streamRpc(string $method, array $parameters = [])
	{
		return @fopen(self::rpcUrl(), 'r', false, self::streamContext($method, $parameters, rand(1, 10000000), 60 * 60));
	}

	protected static function streamContext(string $method, array $parameters, int $callId, int $timeout)
	{
		return stream_context_create([
			'http' => [
				'protocol_version' => 1.1,
				'method' => 'POST',
				'header' => [
					'Content-Type: application/json',
					'Connection: close',
				],
				'timeout' => $timeout,
				'ignore_errors' => true,
				'follow_location' => false,
				'content' => json_encode([
					'jsonrpc' => '2.0',
					'method' => $method,
					'params' => $parameters,
					'id' => $callId,
				]),
			],
		]);
	}

	protected static function rpcUrl(): string
	{
		$url = (string) config('xdag.rpc_url');

		if ($url === '')
			throw new \RuntimeException('XDAG node RPC URL is not set.');

		return $url;
	}
}
