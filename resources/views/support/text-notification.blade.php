<div style="color: {{ $type == 'error' ? 'red' : ($type == 'warning' ? 'orange' : ($type == 'success' ? 'green': 'blue')) }}">
	<span style="font-size:150%">{{ ucfirst($type) }}</span> - {!! $text !!}
</div>
<hr>