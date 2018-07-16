@extends('layouts.text-app')

@section('body')
	@include('block.partials.text-information')

	@if ($block->isTransactionBlock())
		@include('block.partials.text-block-as-transaction')
		@include('block.partials.text-block-as-address')
	@else
		@include('block.partials.text-block-as-address')
		@include('block.partials.text-block-as-transaction')
	@endif
@endsection
