<?php
namespace App\Support\Notification;

class NotificationService
{
	/**
	 * Flash message with type and text
	 *
	 * @param string $type
	 * @param string $typeIcon
	 * @param string $text
	 */
	protected function flash($type, $typeIcon, $text)
	{
		session()->flash('notificationType', $type);
		session()->flash('notificationIcon', $typeIcon);
		session()->flash('notificationMessage', $text);
	}

	/**
	 * Flash success message
	 *
	 * @param string $text
	 */
	public function success($text)
	{
		return $this->flash('success', 'check', $text);
	}

	/**
	 * Flash error message
	 *
	 * @param string $text
	 */
	public function error($text)
	{
		return $this->flash('error', 'alert-circle', $text);
	}

	/**
	 * Flash warning message
	 *
	 * @param string $text
	 */
	public function warning($text)
	{
		return $this->flash('warning', 'alert-triangle', $text);
	}

	/**
	 * Flash info message
	 *
	 * @param string $text
	 */
	public function info($text)
	{
		return $this->flash('info', 'info', $text);
	}
}
