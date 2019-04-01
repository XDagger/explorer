<div class="bg-blue-lightest text-blue-darkest p-4 rounded mb-8">
	<strong class="text-lg block mb-4">Applied filters</strong>

	<div class="flex flex-wrap -mx-4">
		@if ($transactionFilters->hasUsedFilter('address'))
			<div class="w-full md:w-auto md:min-w-xs p-4 pt-0">
				<strong class="block uppercase leading-normal text-sm">Address</strong>
				<span class="block text-sm break-words">{{ $transactionFilters->address }}</span>
			</div>
		@endif

		@if ($transactionFilters->hasUsedFilter('amount_from'))
			<div class="w-full md:w-auto md:min-w-xs p-4 pt-0">
				<strong class="block uppercase leading-normal text-sm">Amount from</strong>
				<span class="block text-sm break-words">{{ $transactionFilters->amountFrom }}</span>
			</div>
		@endif

		@if ($transactionFilters->hasUsedFilter('amount_to'))
			<div class="w-full md:w-auto md:min-w-xs p-4 pt-0">
				<strong class="block uppercase leading-normal text-sm">Amount to</strong>
				<span class="block text-sm break-words">{{ $transactionFilters->amountTo }}</span>
			</div>
		@endif

		@if ($transactionFilters->hasUsedFilter('directions'))
			<div class="w-full md:w-auto md:min-w-xs p-4 pt-0">
				<strong class="block uppercase leading-normal text-sm">Directions</strong>
				<span class="block text-sm break-words">{{ implode(', ', $transactionFilters->directions) }}</span>
			</div>
		@endif
	</div>

	<div class="text-right float-right">
		<a href="/block/{{ $block->getProperties()->get('balance_address') }}#block-as-transaction" rel="nofollow" class="bg-transparent border border-blue text-blue hover:text-blue-dark hover:border-blue-dark text-sm font-medium py-2 px-4 rounded tracking-wide cursor-pointer inline-flex items-center justify-center">
			<span class="h-4 mr-2">
				@svg('x', 'fill-current')
			</span>

			<span>Clear filters</span>
		</a>
	</div>

	@php($filtered_fees = $block->getFilteredFees())
	@php($filtered_inputs = $block->getFilteredInputs())
	@php($filtered_outputs = $block->getFilteredOutputs())

	@if (bccomp($filtered_fees, '0.000000000') > 0 || bccomp($filtered_inputs, '0.000000000') > 0|| bccomp($filtered_outputs, '0.000000000') > 0)
		<strong class="text-lg block mb-4">Filtered totals</strong>
		@if (bccomp($filtered_inputs, '0.000000000') > 0)
			<span class="rounded bg-green uppercase px-3 py-1 text-xs mr-3 text-white text-center mx-auto" title="Total inputs in filtered data" v-tippy>+{{ $filtered_inputs }}</span>
		@endif
		@if (bccomp($filtered_outputs, '0.000000000') > 0)
			<span class="rounded bg-red uppercase px-3 py-1 text-xs mr-3 text-white text-center mx-auto" title="Total outputs in filtered data" v-tippy>-{{ $filtered_outputs }}</span>
		@endif
		@if (bccomp($filtered_fees, '0.000000000') > 0)
			<span class="rounded bg-grey-darker uppercase px-3 py-1 text-xs mr-3 text-white text-center mx-auto" title="Total fees in filtered data" v-tippy>-{{ $filtered_fees }}</span>
		@endif
	@endif

	<div class="clearfix"></div>
</div>
