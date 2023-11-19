@if (isset($uiNotifications) && count($uiNotifications))
	@foreach ($uiNotifications as $uiNotification)
		@include('support.notification', ['type' => $uiNotification['type'], 'text' => $uiNotification['message'], 'delay' => ($uiNotification['timeout'] ?? 10) * 1000])
	@endforeach
@endif

@if (strval(config('explorer.important_ui_message')) !== '')
	@include('support.notification', ['type' => 'info', 'text' => strval(config('explorer.important_ui_message')), 'delay' => 300000])
@endif

