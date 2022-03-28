@extends('layouts.text-app')

@section('body')
	<h2>Balance checker</h2>

	<form action="{{ route('balance checker') }}" method="POST">
		{{ csrf_field() }}

		<label for="address">Wallet address</label><br>
		<input class="form-input" type="text" name="address" id="address" value="{{ request('address') }}">

		<button type="submit">Get balance</button>
	</form>

	@if ($errors->has('address'))
		<br>
		<div>
			<strong>Invalid wallet address.</strong>
		</div>
	@endif

	@if (isset($balance))
		<br>
		<p class="font-bold">Balance on address <strong>{{ request('address') }}</strong> is</p>

		<p class="mt-4 mb-8 text-lg"><strong>{{ $balance }}</strong> XDAG</p>

		<a href="/text/block/{{ request('address') }}" rel="nofollow">Show address</a>
	@endif
@endsection
