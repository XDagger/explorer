<?php
namespace App\Http\Controllers\Wallet;

use App\Xdag\XdagInterface;

use App\Http\Controllers\Controller;
use App\Http\Requests\GetWalletBalanceRequest;

class BalanceCheckerController extends Controller
{
	/**
	 * @return \Illuminate\View\View
	 */
	public function index()
	{
		return view($this->resolveView('balance-checker.index', 'balance-checker.text-index'));
	}

	/**
	 * @param \App\Http\Requests\GetWalletBalanceRequest $request
	 * @param \App\Xdag\XdagInterface					 $xdag
	 *
	 * @return \Illuminate\Http\JsonResponse|\Illuminate\View\View
	 */
	public function getBalance(GetWalletBalanceRequest $request, XdagInterface $xdag)
	{
		$data = [
			'balance' => $xdag->getBalance($request->input('address')),
		];

		if ($this->usingTextView()) {
			return view('balance-checker.text-index', $data);
		}

		return response()->json($data);
	}
}
