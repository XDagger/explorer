<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Xdag\Cache;

class ClearXdagCache extends Command
{
	protected $signature = 'explorer:clear-cache';
	protected $description = 'Clear all expired cache entries and delete cache files older than 1 hour.';

	public function handle()
	{
		Cache::where('expires_at', '<', now())->delete();

		$this->clear(storage_path('cache'), 60);

		$this->info('ClearXdagCache completed successfully.');
		return 0;
	}

	protected function clear($dir, $ttl)
	{
		$dirh = opendir($dir);

		while (($file = readdir($dirh)) !== false) {
			if ($file == '.' || $file == '..' || $file == 'status.json' || $file == '.gitignore')
				continue;

			if (@filetype($dir . '/' . $file) == 'file') {
				$mtime = filemtime($dir . '/' . $file);
				if ($mtime && $mtime <= time() - $ttl * 60)
					unlink($dir . '/' . $file);
			}
		}

		closedir($dirh);
		//$this->line('Cleared dir: ' . $dir);
	}
}
