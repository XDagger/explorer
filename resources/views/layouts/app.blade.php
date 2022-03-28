<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
	<meta name="robots" content="nofollow">
	<title>{{ config('app.name', 'XDAG Block Explorer') }}</title>

	<link rel="stylesheet" href="{{ mix('dist/app.css') }}">
	<link rel="icon" type="image/x-icon" href="/favicon.ico">
</head>
<body class="font-sans antialiased text-black leading-tight bg-grey-lightest cursor-default">

<div id="app" class="flex flex-col min-h-screen">
	@include('layouts.partials.header')

	<div class="flex-1 mt-16">
		@include('support.notifications')

		@yield('body')
	</div>

	@include('layouts.partials.footer')
</div>

<script src="{{ mix('dist/app.js') }}"></script>
</body>
</html>
