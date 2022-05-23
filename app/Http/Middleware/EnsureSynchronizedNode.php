<?php
namespace App\Http\Middleware;

class EnsureSynchronizedNode
{
	public function handle($request, \Closure $next)
	{
		if (false) { // FIXME
			if (request()->wantsJson() || str_starts_with($request->path(), 'api'))
				return response()->json(['error' => 'synchronizing', 'message' => 'Block explorer is currently synchronizing.'], 503);

			return response()->view('errors.synchronizing');
		}

		return $next($request);
	}
}
