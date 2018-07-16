@extends('layouts.text-app')

@section('body')

	<div class="container p-8">
		@include('home.partials.text-network')
		@include('home.partials.text-latest-blocks')
	</div>

@endsection
