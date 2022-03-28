@if (session()->has('notificationMessage'))
	@include('support.text-notification', ['type' => session('notificationType'), 'text' => session('notificationMessage')])
@endif

@if ($errors->has('search'))
	@include('support.text-notification', ['type' => 'error', 'text' => 'Incorrect address, block hash or height.'])
@endif
