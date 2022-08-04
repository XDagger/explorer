@extends('layouts.app')

@section('body')
	<div class="container p-8">
		<div class="box">
			<div class="flex flex-wrap items-start justify-between mb-8">
				<div class="w-full md:w-1/2">
					<h2 class="box-title mb-0">Network information</h2>
				</div>

				<modal inline-template>
					<div class="w-full md:w-auto mt-4 md:mt-0">
						<div class="w-full flex items-center md:justify-end text-blue-dark font-medium cursor-pointer tracking-wide text-center" @click="toggleModal">
							<div class="h-4 mr-2">
								@svg('chart', 'stroke-current')
							</div>

							<span>Details</span>
						</div>

						<transition name="fade">
							<div v-show="modal" @click.self="toggleModal" class="fixed z-50 pin overflow-auto bg-smoke-dark flex" style="display:none">
								<div class="fixed shadow-inner max-w-md relative pin-b pin-x align-top m-auto justify-end md:justify-center p-8 bg-white md:rounded w-full h-auto md:shadow flex flex-col">

									<div>
										<h2 class="text-black text-xl font-bold">Network hashrate</h2>
										<div class="text-grey-dark text-base font-normal mb-8">Last 3 days</div>

										<single-line-chart chart-name="Network Hashrate Mh/s"
											color="blue"
											:class="'w-full h-32 md:h-48'"
											:labels='{{ json_encode($hashrateChartData['labels']) }}'
											:chart-data='{{ json_encode($hashrateChartData['values']) }}'>
										</single-line-chart>


										<h2 class="text-black text-xl font-bold mt-8">New blocks</h2>
										<div class="text-grey-dark text-base font-normal mb-8">Last hour</div>

										<single-bar-chart chart-name="New blocks"
											color="indigo"
											:class="'w-full h-32 md:h-48'"
											:labels='{{ json_encode($newBlocksChartData['labels']) }}'
											:chart-data='{{ json_encode($newBlocksChartData['values']) }}'>
										</single-bar-chart>
									</div>

									<span @click="toggleModal" class="absolute pin-t pin-r pt-4 px-4 text-grey hover:text-grey-darkest cursor-pointer">
										@svg('x', 'w-6 h-6 fill-current')
									</span>
								</div>
							</div>
						</transition>
					</div>
				</modal>
			</div>

			<div class="flex flex-wrap w-full">
				<div class="w-full md:w-1/2">
					<div class="mb-4">
						<div class="info-label flex items-center">
							<span class="mr-2 h-4">
								@svg('arrow-right')
							</span>

							<span>{{ $stat->blocks < $stat->main_blocks? 'Created blocks' : 'Blocks' }}</span>
						</div>
						<span class="info-value">
						</span>

						<div class="flex items-center">
							<span class="info-value mr-4">
								{{ number_format($stat->blocks) }}
							</span>


							<div class="flex items-center text-green text-sm" title="Blocks created in last minute" v-tippy>
								<div class="h-4 w-4">
									@svg('plus', 'stroke-current')
								</div>

								<span class="ml-2">{{ number_format($numberOfNewBlocksLastMinute) }}</span>
							</div>
						</div>
					</div>

					<div class="mb-4">
						<div class="info-label flex items-center">
							<span class="mr-2 h-4">
								@svg('arrow-right')
							</span>

							<span>Main blocks</span>
						</div>
						<span class="info-value">
							<a href="/block/{{ intval($stat->main_blocks) }}" title="Show latest main block" v-tippy>{{ number_format($stat->main_blocks) }}</a>
						</span>
					</div>

					<div class="mb-4 sm:mb-0">
						<div class="info-label flex items-center">
							<span class="mr-2 h-4">
								@svg('arrow-right')
							</span>

							<span>Supply</span>
						</div>
						<span class="info-value">
							{{ number_format($stat->supply) }} XDAG
						</span>
					</div>
				</div>

				<div class="w-full md:w-1/2">
					<div class="mb-4">
						<div class="info-label flex items-center">
							<span class="mr-2 h-4">
								@svg('arrow-right')
							</span>

							<span>Network hashrate</span>
						</div>

						<div class="flex items-center">
							<span class="info-value mr-4">
								{{ hashrate($stat->network_hashrate) }}
							</span>

							@include('support.value-change', ['valueChange' => $hashrateChange, 'name' => 'Network hashrate', 'change' => 'in last hour'])
						</div>
					</div>

					<div>
						<div class="info-label flex items-center">
							<span class="mr-2 h-4">
								@svg('arrow-right')
							</span>

							<span>Difficulty</span>
						</div>

						<span class="info-value block break-words">
							{{ $stat->difficulty }}
						</span>
					</div>
				</div>
			</div>
		</div>

		<div class="box">
			<h2 class="box-title">Latest blocks</h2>
			<div class="box-sub-title">Last 20 main blocks</div>

			<div class="flex flex-wrap w-full">
				@foreach ($mainBlocks->chunk(10) as $chunk)
					<div class="w-full md:w-1/2">
						@foreach($chunk as $mainBlock)
							<div class="mb-4 {{ $loop->last ? 'sm:mb-0' : '' }} sm:flex items-center">
								<div class="mb-2 sm:mb-0 w-full sm:w-1/3 block text-center rounded-full bg-grey-lighter uppercase px-2 py-1 text-xs font-medium mr-3 text-grey-dark" v-tippy title="Height {{ $mainBlock->height }}{!! $mainBlock->remark !== null ? '&lt;br&gt;Found by ' . e(e(linksToDomains($mainBlock->remark))) . '" style="background-color: ' . color($mainBlock->remark) : '' !!}">
									@if (($remarkLink = firstLink($mainBlock->remark)) !== null)
										<a href="{{ $remarkLink }}" target="_blank" style="color: inherit">{{ $mainBlock->created_at->format('Y-m-d H:i:s') }} UTC</a>
									@else
										{{ $mainBlock->created_at->format('Y-m-d H:i:s') }} UTC
									@endif
								</div>

								<div class="w-full sm:w-2/3 sm:mr-2">
									<a href="/block/{{ $mainBlock->address }}" rel="nofollow" class="break-words break-all leading-normal text-sm">
										{{ $mainBlock->address }}
									</a>
								</div>
							</div>
						@endforeach
					</div>
				@endforeach
			</div>
		</div>
	</div>
@endsection
