<?php
namespace App\Support\Api;

final class Response
{
	public function make($data = [], $statusCode = 200)
	{
		return response()->json($data, $statusCode);
	}

	public function noContent()
	{
		return response('', 204);
	}

	public function accepted($location = null, $content = '')
	{
		return response($content, 202, $location ? compact('location') : []);
	}

	public function created($location = null, $content = '')
	{
		return response($content, 201, $location ? compact('location') : []);
	}

	public function error($error, $message, $statusCode)
	{
		return $this->make(compact('error', 'message'), $statusCode);
	}

	public function errorNotFound($message = 'Not Found')
	{
		return $this->error($message, 404);
	}

	public function errorBadRequest($message = 'Bad Request')
	{
		return $this->error($message, 400);
	}

	public function errorForbidden($message = 'Forbidden')
	{
		return $this->error($message, 403);
	}

	public function errorInternal($message = 'Internal Error')
	{
		return $this->error($message, 500);
	}

	public function errorUnauthorized($message = 'Unauthorized')
	{
		return $this->error($message, 401);
	}

	public function errorMethodNotAllowed($message = 'Method Not Allowed')
	{
		return $this->error($message, 405);
	}

	public function teapot($message = 'Hey dude I am teapot')
	{
		return $this->error($message, 418);
	}
}
