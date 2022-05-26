<?php namespace App\Http\Middleware;

use App\Xdag\Network\Stat;

class EnsureSynchronizedNode
{
	public function handle($request, \Closure $next)
	{
		if (!optional(Stat::orderBy('id', 'desc')->limit(1)->first())->synchronized) {
			if (request()->wantsJson() || str_starts_with($request->path(), 'api'))
				return response()->json(['error' => 'synchronizing', 'message' => 'Block explorer is currently synchronizing.'], 503);

			return response()->view('errors.synchronizing');
		}

		return $next($request);
	}
}
