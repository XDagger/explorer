@extends('layouts.app')

@section('body')

@php($fork_height = \App\Xdag\Block\Block::getApolloForkHeight($is_testnet))
@php($already_forked = $network->main_blocks >= $fork_height)
@if ($network->main_blocks >= $fork_height - 1350 * 7 && $network->main_blocks <= $fork_height + 1350 * 7)
	<notification inline-template :delay="60000">
		<transition name="fade">
			<div class="container p-8 pb-0" v-if="showNotification">
				<div class="notification info">
					<div class="w-4 h-4 mr-2">
						{!! svg_image('info', 'stroke-current')->toHtml() !!}
					</div>

					<p class="m-0 font-medium mr-2">
						<strong>Heads up!</strong>
						XDAG Apollo network {{ $already_forked ? 'was' : 'will be' }} activated on {{ $is_testnet ? 'testnet' : '' }} main
						block <strong>{{ $fork_height }}</strong>.
						Block reward {{ $already_forked ? 'was decreased' : 'will decrease' }} to 128 XDAG.
					</p>

					<div class="ml-auto">
						<span class="w-4 h-4 cursor-pointer" @click="hideNotification">
							@svg('x', 'fill-current')
						</span>
					</div>
				</div>
			</div>
		</transition>
	</notification>
@endif

	<div class="container p-8">
		@include('home.partials.network')
		@include('home.partials.latest-blocks')
	</div>

@endsection
