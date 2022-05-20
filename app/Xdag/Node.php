<?php
namespace App\Xdag;

class Node
{
	protected $rpcUrl;

	public function __construct()
	{
		$this->rpcUrl = config('xdag.rpc_url');

		if ((string) $this->rpcUrl === '')
			throw new \RuntimeException('XDAG RPC URL is not set.');
	}
}
