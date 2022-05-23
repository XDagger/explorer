<?php namespace App\Xdag;

use App\Xdag\Exceptions\XdagException;

class Node
{
	protected $rpcUrl;

	public function __construct()
	{
		$this->rpcUrl = config('xdag.rpc_url');

		if ((string) $this->rpcUrl === '')
			throw new \RuntimeException('XDAG node RPC URL is not set.');
	}

	public function callRpc(string $method, array $parameters = []): array
	{
		$response = @file_get_contents($pool['rpc_address'], false, stream_context_create([
			'http' => [
				'method' => 'POST',
				'header' => ['Content-Type: application/json'],
				'timeout' => 5,
				'ignore_errors' => true,
				'follow_location' => false,
				'content' => json_encode([
					'jsonrpc' => '2.0',
					'method' => $method,
					'params' => $parameters,
					'id' => $callId = rand(1, 10000000),
				]),
			],
		]));

		$json = @json_decode((string) $response, true);

		if (!is_array($json) || ($json['jsonrpc'] ?? '') !== '2.0' || ($json['id'] ?? '') !== $callId || !is_array($json['result'] ?? null))
			throw new XdagException("RPC method '$method' returned unexpected response: $response");

		return $json['result'];
	}
}
