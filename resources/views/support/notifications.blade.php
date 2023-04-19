@if (isset($currentErrorMessage))
	@include('support.notification', ['type' => 'error', 'text' => $currentErrorMessage])
@endif

@if (strval(config('explorer.important_ui_message')) !== '')
	@include('support.notification', ['type' => 'info', 'text' => strval(config('explorer.important_ui_message')), 'delay' => 300000])
@endif

