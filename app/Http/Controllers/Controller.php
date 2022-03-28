<?php
namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

use App\Support\Notification\NotificationServiceTrait;

class Controller extends BaseController
{
	use AuthorizesRequests, DispatchesJobs, ValidatesRequests, NotificationServiceTrait;

	/**
	 * @param string $graphicsView
	 * @param string $textView
	 *
	 * @return string
	 */
	public function resolveView($graphicsView, $textView)
	{
		if ($this->usingTextView()) {
			return $textView;
		}

		return $graphicsView;
	}

	/**
	 * @return bool
	 */
	public function usingTextView()
	{
		return str_starts_with(request()->path(), 'text');
	}
}
