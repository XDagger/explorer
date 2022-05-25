<div class="bg-blue-lightest text-blue-darkest p-4 rounded mb-8">
	<strong class="text-lg block mb-4">Applied filters</strong>

	<div class="flex flex-wrap -mx-4">
		@foreach ($filters as $filterKey => $filter)
			<div class="w-full md:w-auto md:min-w-xs p-4 pt-0">
				<strong class="block uppercase leading-normal text-sm">{{ $filter['name'] }}</strong>
				<span class="block text-sm break-words">{{ is_array($filter['value']) ? implode(', ', $filter['value']) : (str_contains($filterKey, '_amount_') ? number_format($filter['value'], 9) : $filter['value']) }}</span>
			</div>
		@endforeach
	</div>

	<div class="text-right float-right">
		<a href="/block/{{ $block->address }}#block-as-address" rel="nofollow" class="bg-transparent border border-blue text-blue hover:text-blue-dark hover:border-blue-dark text-sm font-medium py-2 px-4 rounded tracking-wide cursor-pointer inline-flex items-center justify-center">
			<span class="h-4 mr-2">
				@svg('x', 'fill-current')
			</span>

			<span>Clear filters</span>
		</a>
	</div>

	@php($filteredEarnings = $walletListing->earningsSum())
	@php($filteredSpendings = $walletListing->spendingsSum())

	@if (bccomp($filteredEarnings, '0.000000000') > 0 || bccomp($filteredSpendings, '0.000000000') > 0)
		<strong class="text-lg block mb-4">Filtered totals</strong>
		@if (bccomp($filteredEarnings, '0.000000000') > 0)
			<span class="rounded bg-green uppercase px-3 py-1 text-xs mr-3 text-white text-center mx-auto" title="Total earnings in filtered data" v-tippy>+{{ number_format($filteredEarnings, 9) }}</span>
		@endif
		@if (bccomp($filteredSpendings, '0.000000000') > 0)
			<span class="rounded bg-red uppercase px-3 py-1 text-xs mr-3 text-white text-center mx-auto" title="Total spendings in filtered data" v-tippy>-{{ number_format($filteredSpendings, 9) }}</span>
		@endif
	@endif

	<div class="clearfix"></div>
</div>
