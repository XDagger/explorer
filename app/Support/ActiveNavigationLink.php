<?php
namespace App\Support;

class ActiveNavigationLink
{
	/**
	 * @param string $path
	 *
	 * @return bool
	 */
	public static function checkPath($path)
	{
		return starts_with('/' . request()->path(), $path);
	}

	/**
	 * @param string $route
	 *
	 * @return bool
	 */
	public static function checkRoute($name, $parameters = [])
	{
		return starts_with('/' . request()->path(), route($name, $parameters, false));
	}
}
