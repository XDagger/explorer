<?php
namespace App\Xdag;

use App\Xdag\Block\Output\OutputParser;

interface XdagInterface
{
	public function isReady();

	public function getState();

	public function getVersion();

	public function getLastBlocks($number = 100);

	public function getBalance($address);

	public function getConnections();

	public function getBlock($input, OutputParser $parser);

	public function getStats();
}
