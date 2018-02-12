<?php
namespace App\Service;

class Xdag
{
	protected $socket_file;

	public function __construct($socket_file) {
		if(!extension_loaded('sockets'))
		{
			throw new \Exception('Sockets etension not loaded');
		}

		$this->socket_file = $socket_file;

		if(!$this->isReady()) {
			throw new \Exception('The node is not ready');
		}
	}

	public static function isAddress($address)
	{
		return preg_match("/^[a-zA-Z0-9\/+]{32}$/", $address);
	}

	public function isReady()
	{
		return preg_match("/Normal operation./i", $this->command('state'));
	}

	public function command($cmd)
	{
		$socket = socket_create(AF_UNIX, SOCK_STREAM, 0);

		if (!$socket || !socket_connect($socket, $this->socket_file)) {
			throw new \Exception('Error establishing a connection with the socket');
		}

		$command = "$cmd\0";
		socket_send($socket, $command, strlen($command), 0);

		$output = '';
		while($buffer = @socket_read($socket, 512)) {
				$output .= $buffer;
		}

		socket_close($socket);

		return $output;
	}

	public function commandStream($cmd)
	{
		$socket = socket_create(AF_UNIX, SOCK_STREAM, 0);

		if (!$socket || !socket_connect($socket, $this->socket_file)) {
			throw new \Exception('Error establishing a connection with the socket');
		}

		$command = "$cmd\0";
		socket_send($socket, $command, strlen($command), 0);

		while($line = @socket_read($socket, 512, PHP_NORMAL_READ)) {
			yield $line;
		}

		socket_close($socket);
	}

	public function getBalance($address)
	{
		if (!self::isAddress($address)) {
			throw new \Exception('Invalid address');
		}

		return explode(' ', $this->command("balance $address"))[1];
	}

	public function getBlock($address)
	{
		if (!self::isAddress($address)) {
			throw new \Exception('Invalid address');
		}

		$lines = explode("\n", $this->command("block $address"));
		$block = [];

		$i = 0;
		$block['address'] = [];
		$blockp['transaction'] = [];
		foreach($lines as $line) {
			switch($i) {
				case 0:
						if(preg_match("/Block is not found/i", $line)) {
							throw new \Exception('Block not found');
						} else if(preg_match("/Block as transaction: details/i", $line)) {
							$i++;
						} else if(preg_match("/\s*(.*): ([^\s]*)(\s*([0-9]*\.[0-9]*))?/i", $line, $matches)) {
							list($key, $value) = [$matches[1], $matches[2]];
							if($key == 'balance') $value = $matches[4];
							$block[$key] = $value;
						}
					break;
				case 1:
						if(preg_match("/block as address: details/i", $line)) {
								$i++;
						} else if(preg_match("/\s*(fee|input|output|earning): ([a-zA-Z0-9\/+]{32})\s*([0-9]*\.[0-9]*)/i", $line, $matches)) {
							list(, $direction, $address, $amount) = $matches;
							$block['transaction'][] = ['direction' => $direction, 'address' => $address, 'amount' => $amount];
						}
					break;
				case 2:
					if(preg_match("/\s*(fee|input|output|earning): ([a-zA-Z0-9\/+]{32})\s*([0-9]*\.[0-9]*)\s*(.*)/i", $line, $matches)) {
							list(, $direction, $address, $amount, $time) = $matches;
							$block['address'][] = ['direction' => $direction, 'address' => $address, 'amount' => $amount, 'time' => $time];
					}
					break;
			}
		}
		return $block;
	}

	public function getStats()
	{
		$stats = [];
		$command = $this->command("stats");
		$lines = explode("\n", $command);

		foreach($lines as $line) {
			if(preg_match("/\s*(.*): (.*)/i", $line, $matches)) {
				list($key, $value) = [$matches[1], $matches[2]];
				$stats[$key] = $value;
			}
		}

		return $stats;
	}

	public function getBlocks($stats = '')
	{
		if(!is_array($stats)) {
			$stats = $this->getStats();
		}

		$arr = explode(' ', $stats['blocks']);
		return array_pop($arr);
	}

	public function getMainBlocks($stats = '')
	{
		if(!is_array($stats)) {
			$stats = $this->getStats();
		}

		$arr = explode(' ', $stats['main blocks']);
		return array_pop($arr);
	}

	public function getSupply($stats = '')
	{
		if(!is_array($stats)) {
			$stats = $this->getStats();
		}

		$arr = explode(' ', $stats['XDAG supply']);
		return (int) array_pop($arr);
	}

	public function getHashrate($stats = '')
	{
		if(!is_array($stats)) {
			$stats = $this->getStats();
		}

		$arr = explode(' ', $stats['hour hashrate MHs']);
		return array_pop($arr);
	}

	public function getDifficulty($stats = '')
	{
		if(!is_array($stats)) {
			$stats = $this->getStats();
		}

		$arr = explode(' ', $stats['chain difficulty']);
		return array_pop($arr);
	}
}
