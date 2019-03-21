@extends('layouts.app')

@section('body')
	<div class="container p-8">
		<div class="box relative">
			<h2 class="box-title">Node statistics</h2>

			<div class="absolute pin-t pin-r p-8">
				<span class="w-4 h-4 text-grey" title="Last check at: {{ $lastCheckAt ? $lastCheckAt->toDateTimeString() : 'never' }}" v-tippy>
					@svg('info', 'w-4 h-4')
				</span>
			</div>

			<div class="w-full overflow-auto">
				<table class="w-full">
					<thead>
					<tr>
						<th class="border-b border-grey-lighter p-4 text-left text-black font-bold">Node</th>
						<th class="border-b border-grey-lighter p-4 text-center text-black font-bold">Status</th>
						<th class="border-b border-grey-lighter p-4 text-right">
							<div class="flex items-center justify-end">
								<span class="w-4 h-4" title="Uptime percentage for last 3 months" v-tippy>
									@svg('info', 'w-4 h-4')
								</span>

								<span class="ml-2">Uptime %</span>
							</div>
						</th>
					</tr>
					</thead>

					<tbody>
					@forelse ($nodes as $node)
						<tr>
							<td class="p-4 {{ $loop->index % 2 ? 'bg-grey-lightest' : 'bg-white' }}">
								<div class="flex items-center">
									<div class="w-2 h-2 rounded-full mr-2 {{ $node->is_reachable ? 'bg-green' : 'bg-red' }}"></div>
									<span class="font-medium">{{ $node->node }}</span>
								</div>
							</td>

							<td class="p-4 text-sm {{ $loop->index % 2 ? 'bg-grey-lightest' : 'bg-white' }}">
								@if ($node->is_reachable)
									<div class="flex items-center justify-center text-green">
										@svg('arrow-up', 'w-4 h-4')

										<span class="ml-2">Node is up</span>
									</div>
								@else
									<div class="flex items-center justify-center text-red">
										@svg('arrow-down', 'w-4 h-4')

										<span class="ml-2">Node is down</span>
									</div>
								@endif
							</td>

							<td class="p-4 text-right text-sm {{ $loop->index % 2 ? 'bg-grey-lightest' : 'bg-white' }}">
								{{ number_format($nodeStatisticsRepository->uptimePercentage($node), 2) }}%
							</td>
						</tr>
					@empty
						<tr>
							<td colspan="3" class="p-4 bg-white text-center">There are no node statistics yet.</td>
						</tr>
					@endforelse
					</tbody>
				</table>
			</div>

			{{ $nodes->links('support.pagination') }}
		</div>
	</div>
@endsection
