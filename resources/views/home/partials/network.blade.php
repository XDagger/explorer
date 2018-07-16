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

				@include('home.partials.charts-modal')
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

					<span>Blocks</span>
				</div>
				<span class="info-value">
				</span>

				<div class="flex items-center">
					<span class="info-value mr-4">
						{{ number_format($network->blocks, 0) }}
					</span>


					<div class="flex items-center text-green text-sm" title="Blocks created in last minute" v-tippy>
						<div class="h-4 w-4">
							@svg('plus', 'stroke-current')
						</div>

						<span class="ml-2">{{ number_format($new_blocks) }}</span>
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
					{{ number_format($network->main_blocks, 0) }}
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
					{{ number_format($network->supply, 0) }} XDAG
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
						{{ \App\Xdag\Hashpower::format($network->hashrate, 2) }}
					</span>

					@include('support.value-change', ['valueChange' => $hashrate_change, 'name' => 'Network hashrate', 'change' => 'in last hour'])
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
					{{ $network->difficulty }}
				</span>
			</div>
		</div>
	</div>
</div>
