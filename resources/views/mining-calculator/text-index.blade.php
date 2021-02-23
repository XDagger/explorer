@extends('layouts.text-app')

@section('body')
	<h2>Mining calculator</h2>

	<form action="{{ route('mining calculator') }}" method="POST">
		{{ csrf_field() }}

		<label for="hashrate">Your Hashrate (Kh/s)</label><br>
		<input type="text" name="hashrate" id="hashrate" value="{{ request('hashrate') }}">

		<button type="submit">Estimate</button>
	</form>

	@if (isset($result))
		<br>
		<div>Estimated coins per day <strong>{{ $result }}</strong> XDAG</div>
	@endif
@endsection
