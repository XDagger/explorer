<?php
namespace App\Http\Controllers\Api\Block;

class BlockController extends Controller
{
	public function index(string $id)
	{
		if (strlen($id) < 32 && !ctype_digit($id))
			$id = str_pad($id, 32, '/');
	}

	public function balance(string $id)
	{
		if (strlen($id) < 32)
			$id = str_pad($id, 32, '/');
	}
}
