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

	/* helper methods */
	public function isReady()
	{
		$state = $this->getState();

		return stripos($state, 'normal') !== false || stripos($state, 'transfer to complete') !== false;
	}

	public function versionGreaterThan($version = '0.2.4')
	{
		return version_compare($this->getVersion(), $version) === 1;
	}

	public function isTestnet()
	{
		$state = $this->getState();

		return stripos($state, 'test') !== false;
	}

	/* cached commands */
	public function getState()
	{
		return $this->simpleCachedCommand('state', 1);
	}

	public function getVersion()
	{
		return $this->simpleCachedCommand('version', 30, function ($file_handle) {
			$out = $this->command('version');

			if (stripos($out, 'illegal') === false) {
				$out = [$out];
			} else {
				$file = str_replace('"', '\"', dirname($this->socketFile) . '/xdag');
				exec('"' . $file . '" --help', $out);
			}

			if (!$out) {
				$version = '???';
			} else {
				$line = current($out);
				$line = preg_split('/\s+/', trim($line));

				$version = rtrim(end($line), '.');
			}

			fwrite($file_handle, $version);
		});
	}

	public function getBalance($address)
	{
		if (!Validator::isAddress($address))
			throw new InvalidArgumentException('Invalid address.');

		if (!$this->isReady())
			throw new XdagNodeNotReadyException;

		$output = $this->simpleCachedCommand("balance $address", 3);
		$output = explode(' ', $output);

		return $output[1] ?? '0.000000000';
	}

	public function getBlock($input, OutputParser $parser)
	{
		if (Validator::isHeight($input))
			$input = ltrim($input, '0');

		if (!Validator::isAddress($input) && !Validator::isBlockHash($input) && !Validator::isHeight($input))
			throw new InvalidArgumentException('Invalid address, block hash or height.');

		if (!$this->isReady())
			throw new XdagNodeNotReadyException;

		$parser->setFlippedOutput($this->versionGreaterThan('0.2.4'));

		$base_cmd = Validator::isHeight($input) && $this->versionGreaterThan('0.3.2') ? 'blockbyheight' : 'block';
		$cmd = $base_cmd . ' ' . (Validator::isHeight($input) && !$this->versionGreaterThan('0.3.2') ? 'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA' : $input);

		$reader = function () use ($cmd, $parser, $input) {
			$block = null;

			Cache::read($cmd, function ($file) use ($parser, &$block) {
				$generator = function () use ($file) {
					while (($line = fgets($file, 1024)) !== false) {
						yield $line;
					}
				};

				$block = $parser->getBlockFromOutput($generator());
			});

			if ($block) {
				$has_height = $block->getProperties()->get('height') ? true : false;

				if (Validator::isHeight($input)) {
					Cache::copy($cmd, 'block ' . $block->getProperties()->get('hash'));
					Cache::copy($cmd, 'block ' . $block->getProperties()->get('balance_address'));
				} else if (Validator::isAddress($input)) {
					Cache::copy($cmd, 'block ' . $block->getProperties()->get('hash'));

					if ($has_height) {
						Cache::copy($cmd, 'blockbyheight ' . $block->getProperties()->get('height'));
					}
				} else {
					Cache::copy($cmd, 'block ' . $block->getProperties()->get('balance_address'));

					if ($has_height) {
						Cache::copy($cmd, 'blockbyheight ' . $block->getProperties()->get('height'));
					}
				}
			}

			return $block;
		};

		if ($block = $reader())
			return $block;

		Cache::write($cmd, 3, function($file) use ($cmd) {
			foreach ($this->commandStream($cmd, false) as $chunk) {
				fwrite($file, $chunk);
			}
		});

		return $reader();
	}

	/* non-cached commands */
	public function getLastBlocks($number = 100)
	{
		if (!$this->isReady())
			throw new XdagNodeNotReadyException;

		return $this->commandLines('lastblocks ' . min(100, max(1, intval($number))));
	}

	public function getMainBlocks($number = 100)
	{
		if (!$this->isReady())
			throw new XdagNodeNotReadyException;

		return $this->commandLines('mainblocks ' . min(100, max(1, intval($number))));
	}

	public function getConnections()
	{
		$connections = [];

		foreach ($this->commandLines('net conn') as $line) {
			$line = preg_split('/\s+/', trim($line));

			$count = count($line);

			if ($count !== 11 && $count !== 12)
				continue;

			if ($count == 11) { // pre 0.3.1
				$connections[] = [
					'host' => $line[1],
					'seconds' => (int) $line[2],
					'in_out_bytes' => array_map('intval', explode('/', $line[4])),
					'in_out_packets' => array_map('intval', explode('/', $line[7])),
					'in_out_dropped' => array_map('intval', explode('/', $line[9])),
				];
			} else {
				$connections[] = [
					'host' => $line[1],
					'seconds' => (int) $line[2],
					'in_out_bytes' => array_map('intval', explode('/', $line[6])),
					'in_out_packets' => array_map('intval', explode('/', $line[8])),
					'in_out_dropped' => array_map('intval', explode('/', $line[10])),
				];
			}
		}

		return $connections;
	}

	public function getStats()
	{
		$stats = [];

		foreach ($this->commandLines('stats') as $line) {
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

	/* daemon communication functions */
	protected function command($cmd)
	{
		$output = '';

		foreach ($this->commandStream($cmd, false) as $data)
			$output.= $data;

		return $output;
	}

	protected function commandLines($cmd)
	{
		$lines = [];

		foreach ($this->commandStream($cmd, true) as $line)
			$lines[] = $line;

		return $lines;
	}

	protected function commandStream($cmd, $read_lines)
	{
		$socket = @socket_create(AF_UNIX, SOCK_STREAM, 0);

		if (!$socket || !@socket_connect($socket, $this->socketFile))
			throw new XdagException('Error establishing a connection with the socket');

		$command = "$cmd\0";
		socket_send($socket, $command, strlen($command), 0);

		if ($read_lines) {
			while ($line = @socket_read($socket, 1024, PHP_NORMAL_READ)) {
				yield rtrim($line, "\n");
			}
		} else {
			while ($data = @socket_read($socket, 16384, PHP_BINARY_READ)) {
				yield $data;
			}
		}

		socket_close($socket);
	}

	/* cache helper for simple commands whose output fits in memory */
	protected function simpleCachedCommand($cmd, $ttl, callable $output_generator = null)
	{
		$reader = function () use ($cmd) {
			$output = null;

			Cache::read($cmd, function ($file) use (&$output) {
				while (!feof($file))
					$output .= fread($file, 16384);
			});

			return $output;
		};

		if ($output = $reader())
			return $output;

		Cache::write($cmd, $ttl, $output_generator ?? function ($file) use ($cmd) {
			fwrite($file, $this->command($cmd));
		});

		return $reader();
	}
}
