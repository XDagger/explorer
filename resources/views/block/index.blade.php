@extends('layouts.app')

@section('body')

	<div class="container px-8 pt-8">
		@include('block.partials.information')

		@if ($block->isTransactionBlock())
			@include('block.partials.block-as-transaction')
			@include('block.partials.block-as-address')
		@else
			@include('block.partials.block-as-address')
			@include('block.partials.block-as-transaction')
		@endif
	</div>

@endsection
