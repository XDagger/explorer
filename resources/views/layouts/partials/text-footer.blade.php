<br>
<br>
<hr>
<br>
@php($url = request()->getPathInfo())
@if (str_starts_with($url, '/text'))
	@php($url = substr($url, 5))
	@if ($url === '')
		@php($url = '/')
	@endif
@endif
<a href="{{ $url }}" rel="nofollow">Graphics view</a>
<a href="{{ route('api docs') }}">API Docs</a>
<br>
<br>
Copyright &copy; {{ date('Y') }}
<!-- This page took {{ number_format((microtime(true) - LARAVEL_START), 3) }} seconds to render -->
