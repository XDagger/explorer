@extends('layouts.app')

@section('body')
	<div class="container p-8">
		<div class="box api-docs">
			{!! $content !!}
		</div>
	</div>
@endsection
