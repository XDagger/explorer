@foreach (['error', 'info', 'warning', 'success'] as $notificationType)
	@if (session($notificationType))
		@include('support.notification', ['type' => $notificationType, 'text' => session($notificationType)])
	@endif
@endforeach

@if (strval(config('explorer.important_ui_message')) !== '')
	@include('support.notification', ['type' => 'info', 'text' => strval(config('explorer.important_ui_message')), 'delay' => 300000])
@endif

