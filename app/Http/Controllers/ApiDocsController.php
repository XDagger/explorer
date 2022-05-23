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

		return view('api-docs.index', [
			'content' => $parsedown->text($apiDocsMarkdown)
		]);
	}
}
