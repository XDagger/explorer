<div class="flex flex-wrap">
	<div class="w-full md:w-2/3 md:pr-4">
		<div class="box">
			<div class="flex flex-wrap items-start justify-between mb-8">
				<div class="w-full md:w-1/2">
					<h2 class="box-title mb-0">Block Information</h2>
				</div>
			</div>

			<div class="mb-4">
				<div class="flex flex-wrap justify-between items-start">
					<div class="mb-4 md:mb-0 w-full md:w-1/2">
						<strong class="info-label">Date and time (UTC)</strong>
						<span class="info-value">{{ $block->created_at->format('Y-m-d H:i.s.v') }}</span>
					</div>

					<div class="w-full md:w-1/2">
						<strong class="info-label">State</strong>
						<span class="info-value">{{ $block->state }}</span>
					</div>
				</div>
			</div>

			<div class="mb-4">
				<strong class="info-label">Hash</strong>
				@if ($block->hash !== null)
					<a href="{{ route('block', ['id' => $block->hash]) }}" rel="nofollow" class="leading-normal opacity-75 block break-words">{{ $block->hash }}</a>
				@else
					-
				@endif
			</div>

			<div class="mb-4">
				@if ($block->remark !== null)
					<div class="flex flex-wrap justify-between items-start">
						<div class="mb-4 md:mb-0 w-full md:w-1/2">
							<strong class="info-label">Address</strong>
							<a href="/block/{{ $block->address }}" rel="nofollow" class="leading-normal opacity-75 block break-words">{{ $block->address }}</a>
						</div>

						<div class="w-full md:w-1/2">
							<strong class="info-label">Remark</strong>
							<span class="info-value">{!! clickableFullLinks($block->remark) !!}</span>
						</div>
					</div>
				@else
					<strong class="info-label">Address</strong>
					<a href="/block/{{ $block->address }}" rel="nofollow" class="leading-normal opacity-75 block break-words">{{ $block->address }}</a>
				@endif
			</div>

			<div class="flex flex-wrap justify-between items-start">
				<div class="mb-4 md:mb-0 w-full md:w-1/2">
					<strong class="info-label">Difficulty</strong>
				<span class="info-value block break-words">{{ $block->difficulty ?? '-' }}</span>
				</div>

				<div class="w-full md:w-1/2">
					<strong class="info-label">Type</strong>
					@if ($block->height > 0)
						<div class="flex items-center justify-between">
							<a href="/block/{{ $block->height }}" rel="nofollow" class="leading-normal opacity-75 break-words text-grey-darkest">Main block {{ $block->height }}</a>

							<div class="flex items-center">
								@if ($block->height > 1)
									<a href="/block/{{ $block->height - 1 }}" rel="nofollow" class="mr-2" title="Previous main block" v-tippy>@svg('arrow-left', 'stroke-current')</a>
								@endif
								<a href="/block/{{ $block->height + 1 }}" rel="nofollow" title="Next main block" v-tippy>@svg('arrow-right', 'stroke-current')</a>
							</div>
						</div>
					@else
						<span class="info-value">{{ $block->type }}</span>
					@endif
				</div>
			</div>
		</div>
	</div>

	@if ($block->isTransactionBlock())
		<div class="w-full md:w-1/3 md:pl-4">
			<div class="box">
				<h3 class="box-title">Summary</h3>

				<div class="mb-4">
					<div class="flex items-center justify-between">
						<div class="mr-4">
							<div class="info-label">Total fees</div>
							<span class="info-value">{{ number_format($block->transactions()->transaction()->whereDirection('fee')->sum(DB::raw('ABS(amount)')), 9) }}</span>
						</div>
					</div>
				</div>

				<div class="mb-4">
					<div class="flex items-center justify-between">
						<div class="mr-4">
							<div class="info-label">{{ $count = $block->transactions()->transaction()->whereDirection('input')->count() }} input{{ $count > 1 ? 's' : '' }}</div>
							<span class="info-value">{{ number_format($block->transactions()->transaction()->whereDirection('input')->sum('amount'), 9) }}</span>
						</div>
					</div>
				</div>

				<div class="mb-4">
					<div class="flex items-center justify-between">
						<div class="mr-4">
							<div class="info-label">{{ $count = $block->transactions()->transaction()->whereDirection('output')->count() }} output{{ $count > 1 ? 's' : '' }}</div>
							<span class="info-value">{{ number_format($block->transactions()->transaction()->whereDirection('output')->sum(DB::raw('ABS(amount)')), 9) }}</span>
						</div>
					</div>
				</div>
			</div>
		</div>
	@else
		<div class="w-full md:w-1/3 md:pl-4">
			<div class="box">
				<h3 class="box-title">Balances</h3>

				<div class="mb-4">
					<div class="flex items-center justify-between">
						<div class="mr-4">
							<div class="info-label">Balance</div>
							<span class="info-value">{{ number_format($block->balance, 9) }}</span>
						</div>

						@include('support.value-change', ['valueChange' => $balanceChange, 'name' => 'Balance', 'change' => 'since 24 hours ago', 'type' => 'value'])
					</div>

					<modal inline-template>
						<div>
							<div class="w-full flex items-center text-blue-dark font-medium cursor-pointer tracking-wide text-center text-sm" @click="toggleModal">
								<div class="h-4 mr-2">
									@svg('chart', 'stroke-current w-3 h-3')
								</div>

								<span>Details</span>
							</div>

							@include('block.partials.balance-modal')
						</div>
					</modal>
				</div>

				<div class="mb-4">
					<div class="flex items-center justify-between">
						<div class="mr-4">
							<div class="info-label">Total Earnings</div>
							<span class="info-value">{{ number_format($block->transactions()->wallet()->earnings()->sum('amount'), 9) }}</span>
						</div>

						@include('support.value-change', ['valueChange' => $earningsChange, 'name' => 'Earnings', 'change' => 'since 24 hours ago', 'type' => 'value'])
					</div>

					<modal inline-template>
						<div>
							<div class="w-full flex items-center text-blue-dark font-medium cursor-pointer tracking-wide text-center text-sm" @click="toggleModal">
								<div class="h-4 mr-2">
									@svg('chart', 'stroke-current w-3 h-3')
								</div>

								<span>Details</span>
							</div>

							@include('block.partials.earnings-modal')
						</div>
					</modal>
				</div>

				<div>
					<div class="flex items-center justify-between">
						<div class="mr-4">
							<div class="info-label">Total Spendings</div>
							<span class="info-value">{{ number_format($block->transactions()->wallet()->spendings()->sum(DB::raw('ABS(amount)')), 9) }}</span>
						</div>

						@include('support.value-change', ['valueChange' => $spendingsChange, 'name' => 'Spendings', 'change' => 'since 24 hours ago', 'type' => 'value'])
					</div>

					<modal inline-template>
						<div>
							<div class="w-full flex items-center text-blue-dark font-medium cursor-pointer tracking-wide text-center text-sm" @click="toggleModal">
								<div class="h-4 mr-2">
									@svg('chart', 'stroke-current w-3 h-3')
								</div>

								<span>Details</span>
							</div>

							@include('block.partials.spendings-modal')
						</div>
					</modal>
				</div>
			</div>
		</div>
	@endif
</div>
