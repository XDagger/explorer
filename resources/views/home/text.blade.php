@extends('layouts.text-app')

@section('body')

@php($fork_height = \App\Xdag\Block\Block::getRandomXForkHeight($is_testnet))
@php($already_forked = $network->main_blocks >= $fork_height)
@if ($network->main_blocks >= $fork_height - 1350 * 10 && $network->main_blocks <= $fork_height + 1350 * 10)
	<div style="color: blue">
		<span style="font-size: 150%">Heads up!</span> -
			XDAG {{ $already_forked ? 'switched' : 'will switch' }} to RandomX mining algorithm on {{ $is_testnet ? 'testnet' : '' }} main
			block <strong>{{ $fork_height }}</strong>.
			RandomX miner must be used after the switch, downloads available on
			<a href="https://github.com/swordlet/DaggerRandomxMiner/releases" target="_blank" rel="nofollow">GitHub</a>.
	</div>
	<hr>
@endif

	<div class="container p-8">
		@include('home.partials.text-network')
		@include('home.partials.text-latest-blocks')
	</div>

@endsection
