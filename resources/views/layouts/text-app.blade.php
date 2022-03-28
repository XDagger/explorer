<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
	<meta name="robots" content="nofollow">
	<title>{{ config('app.name', 'XDAG Block Explorer') }}</title>
</head>
<body style="font-family:monospace">
@include('layouts.partials.text-header')
@include('support.text-notifications')
@yield('body')
@include('layouts.partials.text-footer')
</body>
</html>
