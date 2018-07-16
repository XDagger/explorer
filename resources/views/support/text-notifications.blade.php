@if (session()->has('notificationMessage'))
	@include('support.text-notification', ['type' => session('notificationType'), 'text' => session('notificationMessage')])
@endif

@if ($errors->has('search_address_or_hash'))
	@include('support.text-notification', ['type' => 'error', 'text' => 'Incorrect address or block hash.'])
@endif
