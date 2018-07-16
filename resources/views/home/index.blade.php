@extends('layouts.app')

@section('body')

	<div class="container p-8">
		@include('home.partials.network')
		@include('home.partials.latest-blocks')
	</div>

@endsection
