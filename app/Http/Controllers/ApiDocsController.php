<?php namespace App\Http\Controllers;

use App\Xdag\Block\MainBlock;

class ApiDocsController extends Controller
{
	public function index(\Parsedown $parsedown)
	{
		return view('api-docs.index', [
			'content' => str_replace('~LATEST_MAIN_BLOCK_ADDRESS~', optional(MainBlock::orderBy('height', 'desc')->limit(1)->first())->address ?? '1', $parsedown->text(file_get_contents(base_path('API.md'))))
		]);
	}
}
