<?php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Xdag\Xdag;
use App\Xdag\XdagLocal;
use App\Xdag\XdagInterface;

class XdagServiceProvider extends ServiceProvider
{
	/**
	 * Register services.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->app->bind(XdagInterface::class, function ($app) {
			if ($app['config']['services']['xdag']['real']) {
				return new Xdag($app['config']['services']['xdag']['socket_file']);
			}

			return new XdagLocal();
		});
	}
}
