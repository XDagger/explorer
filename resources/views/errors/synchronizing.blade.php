<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
	<title>{{ config('app.name', 'XDag Explorer') }}</title>

	<link rel="stylesheet" href="{{ mix('dist/app.css') }}">
</head>
<body class="font-sans antialiased text-white bg-blue-darkest leading-tight cursor-default">

<div id="app" class="flex items-center justify-center min-h-screen">

	<div class="container">
		<a href="{{ route('home') }}" rel="nofollow" class="no-underline flex items-center justify-center text-white hover:text-blue-lighter scale transition tracking-wide">
			<img src="/images/xdag.png" class="w-16 h-16" style="transform: translateZ(0) scale(0.999999)">

			<h1 class="pl-4 text-3xl">{{ $appName }}</h1>
		</a>

		<div class="box border-red mt-8 text-center">
			<h2 class="text-xl font-medium leading-loose">Block explorer is currently synchronizing...</h2>
			<p class="leading-loose">Synchronization process should be finished shortly. Please wait a bit and refresh the page.</p>
			<p class="mb-8 text-grey text-sm">Current time: {{ now()->toDateTimeString() }} UTC</p>

			<a class="button primary inline-flex items-center" href="javascript:location.reload()" rel="nofollow">
				<span class="h-4 w-4 mr-2">
					@svg('refresh-cw')
				</span>

				<span>Refresh page</span>
			</a>
		</div>
	</div>

</div>

</body>
</html>
