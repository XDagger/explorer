<?php
namespace App\Xdag;

use InvalidArgumentException;
use App\Xdag\Exceptions\{XdagException, XdagNodeNotReadyException};

use App\Xdag\Block\Validation\Validator;
use App\Xdag\Block\Output\OutputParser;

class Xdag implements XdagInterface
{
	protected $socketFile;

	public function __construct($socketFile)
	{
		if (! extension_loaded('sockets')) {
			throw new XdagException('Sockets extension not loaded.');
		}

		$this->socketFile = $socketFile;
	}

	public function isReady()
	{
		$state = $this->getState();

		return stripos($state, 'normal') !== false || stripos($state, 'transfer to complete') !== false;
	}

	public function getState()
	{
		return $this->command('state');
	}

	public function versionGreaterThan($version = '0.2.4')
	{
		return version_compare($this->getVersion(), $version) === 1;
	}

	public function getVersion($provided = null)
	{
		$cache = new Cache();
		if (($file = $cache->open('version', 30)) !== false) {
			$version = trim(fgets($file));
			fclose($file);
			return $version;
		}

		if ($provided === null) {
			$file = str_replace('"', '\"', dirname($this->socketFile) . '/xdag');
			exec('"' . $file . '"', $out);

			if (! $out) {
				$version = '???';
			} else {
				$line = current($out);
				$line = preg_split('/\s+/', trim($line));

				$version = rtrim(end($line), '.');
			}
		} else {
			$version = $provided;
		}

		if (($file = $cache->create('version', 30)) !== false) {
			fwrite($file, $version);
			$cache->finishCreate('version', 30);
		}

		return $version;
	}

	public function getLastBlocks($number = 100)
	{
		if (! $this->isReady()) {
			throw new XdagNodeNotReadyException;
		}

		return $this->commandStream('lastblocks ' . min(100, max(1, intval($number))));
	}

	public function getMainBlocks($number = 100)
	{
		if (! $this->isReady()) {
			throw new XdagNodeNotReadyException;
		}

		return $this->commandStream('mainblocks ' . min(100, max(1, intval($number))));
	}

	public function getBlock($input, OutputParser $parser)
	{
		if (!Validator::isAddress($input) && !Validator::isBlockHash($input)) {
			throw new InvalidArgumentException('Invalid address or block hash.');
		}

		if (! $this->isReady()) {
			throw new XdagNodeNotReadyException;
		}

		$block = $parser->getBlockFromOutput($this->commandStream('block ' . $input), $this->versionGreaterThan('0.2.4'));

		if (!$block)
			return $block;

		$cache = new Cache;
		if (Validator::isBlockHash($input)) {
			$cmd = 'block ' . $block->getProperties()->get('balance_address');
		} else {
			$cmd = 'block ' . $block->getProperties()->get('hash');
		}

		if ($file = $cache->create($cmd, 3)) {
			$other_file = @fopen($cache->getPath(3) . '/' . md5('block ' . $input), 'rb');
			if ($other_file) {
				while (!feof($other_file)) {
					fwrite($file, fread($other_file, 4096));
				}

				fclose($other_file);
				$cache->finishCreate($cmd, 3);
			}
		}

		return $block;
	}

	public function getBalance($address)
	{
		if (! Validator::isAddress($address)) {
			throw new InvalidArgumentException('Invalid address.');
		}

		if (! $this->isReady()) {
			throw new XdagNodeNotReadyException;
		}

		$output = $this->command("balance $address");
		$output = explode(' ', $output);

		return $output[1] ?? '0.000000000';
	}

	public function getConnections()
	{
		$connections = [];

		foreach ($this->commandStream('net conn') as $line) {
			$line = preg_split('/\s+/', trim($line));

			if (count($line) != 11) {
				continue;
			}

			$connections[] = [
				'host' => $line[1],
				'seconds' => (int)$line[2],
				'in_out_bytes' => array_map('intval', explode('/', $line[4])),
				'in_out_packets' => array_map('intval', explode('/', $line[7])),
				'in_out_dropped' => array_map('intval', explode('/', $line[9])),
			];
		}

		return $connections;
	}

	public function getStats()
	{
		$stats = [];

		foreach ($this->commandStream('stats') as $line) {
			if (preg_match('/\s*(.*): (.*)/i', $line, $matches)) {
				$key	= strtolower(trim($matches[1]));
				$values = explode(' of ', $raw_value = strtolower(trim($matches[2])));

				if (count($values) == 2) {
					if ($key !== 'chain difficulty') {
						foreach ($values as $i => $value) {
							if (preg_match('/^[0-9]+$/', $value)) {
								$values[$i] = (int) $value;
							} else if (is_numeric($value)) {
								$values[$i] = (float) $value;
							}
						}
					}

					$stats[str_replace(' ', '_', $key)] = $values;

					if (strpos($key, 'hashrate') !== false && ! isset($stats['hashrate'])) {
						$stats['hashrate'] = [$values[0] * 1024 * 1024, $values[1] * 1024 * 1024];
					}
				} else {
					if (preg_match('/^[0-9]+$/', $raw_value)) {
						$raw_value = (int)$raw_value;
					} else if (is_numeric($raw_value)) {
						$raw_value = (float)$raw_value;
					}

					$stats[str_replace(' ', '_', $key)] = $raw_value;
				}
			}
		}

		return $stats;
	}

	protected function command($cmd)
	{
		$lines = [];
		foreach ($this->commandStream($cmd) as $line) {
			$lines[] = $line;
		}

		return implode("\n", $lines);
	}

	protected function commandStream($cmd, $generator = null)
	{
		$command = explode(' ', $cmd)[0];
		$ttl_map = ['block' => 3, 'stats' => 0];
		$ttl = $ttl_map[$command] ?? 1;

		if ($ttl == 0)
			return $this->directCommandStream($cmd, $generator);

		$cache = new Cache();

		if (($file = $cache->open($cmd, $ttl)) !== false)
			return $this->cachedCommandStream($file);

		if (($file = $cache->create($cmd, $ttl)) !== false)
			$this->commandStreamToCache($cache, $ttl, $file, $cmd, $generator);
		else
			throw new XdagException('Unable to write cache for command "' . $cmd . '" with ttl ' . $ttl);

		$file = $cache->open($cmd, $ttl);
		return $this->cachedCommandStream($file);
	}

	protected function directCommandStream($cmd, $generator = null, $chunk_read = false)
	{
		if (!$generator) {
			$socket = @socket_create(AF_UNIX, SOCK_STREAM, 0);

			if (! $socket || ! @socket_connect($socket, $this->socketFile)) {
				throw new XdagException('Error establishing a connection with the socket');
			}

			$command = "$cmd\0";
			socket_send($socket, $command, strlen($command), 0);

			if (!$chunk_read) {
				while ($line = @socket_read($socket, 1024, PHP_NORMAL_READ)) {
					yield rtrim($line, "\n");
				}
			} else {
				while ($data = @socket_read($socket, 4096, PHP_BINARY_READ)) {
					yield $data;
				}
			}

			socket_close($socket);
		} else {
			foreach ($generator as $line) {
				yield rtrim($line, "\n");
			}
		}
	}

	protected function cachedCommandStream($file)
	{
		while (($line = fgets($file)) !== false)
			yield rtrim($line, "\n");

		fclose($file);
	}

	protected function commandStreamToCache($cache, $ttl, $file, $cmd, $generator = null)
	{
		$chunk_read = false;

		if (!$generator) {
			$generator = $this->directCommandStream($cmd, null, true);
			$chunk_read = true;
		}

		foreach ($generator as $data) {
			if (!$chunk_read) {
				fwrite($file, $data . "\n");
			} else {
				fwrite($file, $data);
			}
		}

		fclose($file);

		$cache->finishCreate($cmd, $ttl);
	}
}
