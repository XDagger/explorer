<?php namespace App\Providers;

use App\Xdag\Network\Stat;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
	/**
	 * Register any application services.
	 *
	 * @return void
	 */
	public function register()
	{
		//
	}

	/**
	 * Bootstrap any application services.
	 *
	 * @return void
	 */
	public function boot()
	{
		require_once app_path('helpers.php');

		Blade::directive('svg', function ($expression) {
			$tokens = collect(explode(',', $expression))->map(function ($item) {
				return trim(trim($item), "'\"");
			});

			if (!$tokens->count())
				return $expression;

			if (!is_readable($path = resource_path("assets/svg/{$tokens->first()}.svg")))
				return $path;

			$class = trim('w-4 h-4 ' . ($tokens->get(1) ?? ''));

			return str_replace('<svg', '<svg class="' . $class . '"', file_get_contents($path));
		});

		try {
			$stat = Stat::orderBy('id', 'desc')->limit(1)->first();
		} catch (\Illuminate\Database\QueryException $ex) {
			$stat = null;
		}

		View::share('appName', (isset($stat) && $stat->network_type !== 'mainnet') ? 'XDAG ' . ucfirst($stat->network_type) . ' Explorer' : 'XDAG Block Explorer');
	}
}
