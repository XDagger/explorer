<?php
namespace App\Http\Controllers;

use Parsedown;

class ApiDocsController extends Controller
{
	/**
	 * @return \Illuminate\View\View
	 */
	public function index(Parsedown $parsedown)
	{
		$apiDocsMarkdown = file_get_contents(base_path('API.md'));

		return view($this->resolveView('api-docs.index', 'api-docs.text-index'), [
			'content' => $parsedown->text($apiDocsMarkdown)
		]);
	}
}
