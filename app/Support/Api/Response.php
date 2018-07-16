<?php
namespace App\Support\Api;

use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

final class Response
{
	/**
	 * Create json response
	 *
	 * @param mixed $data
	 * @param int	$statusCode
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function make($data = [], $statusCode = SymfonyResponse::HTTP_OK)
	{
		return response()->json($data, $statusCode);
	}

	/**
	 * Return 204 http response without content.
	 *
	 * @return \Illuminate\Contracts\Routing\ResponseFactory|SymfonyResponse
	 */
	public function noContent()
	{
		return response('', SymfonyResponse::HTTP_NO_CONTENT);
	}

	/**
	 * Return 202 http response with optional location and content
	 *
	 * @param null	 $location
	 * @param string $content
	 *
	 * @return \Illuminate\Contracts\Routing\ResponseFactory|SymfonyResponse
	 */
	public function accepted($location = null, $content = '')
	{
		return response($content, SymfonyResponse::HTTP_ACCEPTED, $location ? compact('location') : []);
	}

	/**
	 * Return 201 http response with optional location and content
	 *
	 * @param null	 $location
	 * @param string $content
	 *
	 * @return \Illuminate\Contracts\Routing\ResponseFactory|SymfonyResponse
	 */
	public function created($location = null, $content = '')
	{
		return response($content, SymfonyResponse::HTTP_CREATED, $location ? compact('location') : []);
	}

	/**
	 * Return an error response.
	 *
	 * @param string $error
	 * @param int	 $statusCode
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function error($error, $message, $statusCode)
	{
		return $this->make(compact('error', 'message'), $statusCode);
	}

	/**
	 * Return a 404 not found error.
	 *
	 * @param string $message
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function errorNotFound($message = 'Not Found')
	{
		return $this->error($message, SymfonyResponse::HTTP_NOT_FOUND);
	}

	/**
	 * Return a 400 bad request error.
	 *
	 * @param string $message
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function errorBadRequest($message = 'Bad Request')
	{
		return $this->error($message, SymfonyResponse::HTTP_BAD_REQUEST);
	}

	/**
	 * Return a 403 forbidden error.
	 *
	 * @param string $message
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function errorForbidden($message = 'Forbidden')
	{
		return $this->error($message, SymfonyResponse::HTTP_FORBIDDEN);
	}

	/**
	 * Return a 500 internal server error.
	 *
	 * @param string $message
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function errorInternal($message = 'Internal Error')
	{
		return $this->error($message, SymfonyResponse::HTTP_INTERNAL_SERVER_ERROR);
	}

	/**
	 * Return a 401 unauthorized error.
	 *
	 * @param string $message
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function errorUnauthorized($message = 'Unauthorized')
	{
		return $this->error($message, SymfonyResponse::HTTP_UNAUTHORIZED);
	}

	/**
	 * Return a 405 method not allowed error.
	 *
	 * @param string $message
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function errorMethodNotAllowed($message = 'Method Not Allowed')
	{
		return $this->error($message, SymfonyResponse::HTTP_METHOD_NOT_ALLOWED);
	}

	/**
	 * 418 Response, because why not
	 *
	 * @param string $message
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function teapot($message = 'Hey dude I am teapot')
	{
		return $this->error($message, SymfonyResponse::HTTP_I_AM_A_TEAPOT);
	}
}
