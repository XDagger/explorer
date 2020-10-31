@if (session()->has('notificationMessage'))
	@include('support.notification', ['type' => session('notificationType'), 'icon' => session('notificationIcon'), 'iconClass' => 'stroke-current', 'text' => session('notificationMessage')])
@endif

@if ($errors->has('search'))
	@include('support.notification', ['type' => 'error', 'icon' => 'alert-circle', 'iconClass' => 'stroke-current', 'text' => 'Incorrect address, block hash or height.'])
@endif
