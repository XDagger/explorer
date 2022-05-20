<?php
namespace App\Http\Controllers;

use App\Http\Requests\BalanceRequest;

class BalanceController extends Controller
{
	public function index()
	{
		return view('balance-checker.index');
	}

	public function getBalance(BalanceRequest $request)
	{
		return response()->json(['balance' => $xdag->getBalance($request->input('input'))]);
	}
}
