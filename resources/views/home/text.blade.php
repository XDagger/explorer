@extends('layouts.text-app')

@section('body')

@php($fork_height = \App\Xdag\Block\Block::getApolloForkHeight($is_testnet))
@php($already_forked = $network->main_blocks >= $fork_height)
@if ($network->main_blocks >= $fork_height - 1350 * 7 && $network->main_blocks <= $fork_height + 1350 * 7)
	<div style="color: blue">
		<span style="font-size: 150%">Heads up!</span> -
			XDAG Apollo network {{ $already_forked ? 'was' : 'will be' }} activated on {{ $is_testnet ? 'testnet' : '' }} main
			block <strong>{{ $fork_height }}</strong>.
			Block reward {{ $already_forked ? 'was decreased' : 'will decrease' }} to 128 XDAG.
	</div>
	<hr>
@endif

	<div class="container p-8">
		@include('home.partials.text-network')
		@include('home.partials.text-latest-blocks')
	</div>

@endsection
