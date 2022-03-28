<?php
namespace App\Support\Notification;

trait NotificationServiceTrait
{
	/**
	 * @return NotificationService
	 */
	public function notify()
	{
		static $notificationService;

		if (is_null($notificationService)) {
			$notificationService = resolve(NotificationService::class);
		}

		return $notificationService;
	}
}
