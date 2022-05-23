@foreach (['error', 'info', 'warning', 'success'] as $notificationType)
	@if (session($notificationType))
		@include('support.notification', ['type' => $notificationType, 'text' => session($notificationType)])
	@endif
@endforeach

