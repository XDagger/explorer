<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ClearXdagCache extends Command
{
	protected $signature = 'explorer:clear-cache {--full}';
	protected $description = 'Clear expired or all Xdag cache entries (with --full argument).';

	public function handle()
	{
		$storage_path = storage_path('cache');
		$dir = opendir($storage_path);

		while (($file = readdir($dir)) !== false) {
			if ($file == '.' || $file == '..')
				continue;

			if (filetype($storage_path . '/' . $file) == 'dir') {
				$this->clear($storage_path . '/' . $file, $this->option('full') ? 0 : (int) $file);
			}
		}

		closedir($dir);

		$this->info('ClearXdagCache completed successfully.');
	}

	protected function clear($dir, $ttl)
	{
		$dirh = opendir($dir);

		while (($file = readdir($dirh)) !== false) {
			if ($file == '.' || $file == '..')
				continue;

			if (filetype($dir . '/' . $file) == 'file') {
				$mtime = filemtime($dir . '/' . $file);
				if ($mtime && $mtime <= time() - $ttl * 60)
					unlink($dir . '/' . $file);
			}
		}

		closedir($dirh);
		//$this->line('Cleared dir: ' . $dir);
	}
}
