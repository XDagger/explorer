<?php
namespace App\Http\Middleware;

use Closure;

use Symfony\Component\HttpFoundation\Response;

use App\Xdag\XdagInterface;

class XdagStateMiddlware
{
	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request $request
	 * @param  \Closure					$next
	 *
	 * @return mixed
	 */
	public function handle($request, Closure $next)
	{
		if (starts_with($request->path(), 'api/status')) {
			// /api/status is always available
			return $next($request);
		}

		if (! resolve(XdagInterface::class)->isReady()) {
			if (request()->wantsJson() || starts_with($request->path(), 'api')) {
				return $this->jsonResponse();
			}

			return response()->view('errors.synchronizing');
		}

		return $next($request);
	}

	/**
	 * @return \Illuminate\Http\JsonResponse
	 */
	protected function jsonResponse()
	{
		return (new \App\Support\Api\Response())->error(
			'synchronizing',
			'Block explorer is currently synchronizing.',
			Response::HTTP_SERVICE_UNAVAILABLE
		);
	}
}
