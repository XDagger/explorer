<div class="bg-blue-lightest text-blue-darkest p-4 rounded mb-8">
	<strong class="text-lg block mb-4">Applied filters</strong>

	<div class="flex flex-wrap -mx-4">
		@if ($addressFilters->hasUsedFilter('address'))
			<div class="w-full md:w-auto md:min-w-xs p-4 pt-0">
				<strong class="block uppercase leading-normal text-sm">Address</strong>
				<span class="block text-sm break-words">{{ $addressFilters->address }}</span>
			</div>
		@endif

		@if ($addressFilters->hasUsedFilter('date_from'))
			<div class="w-full md:w-auto md:min-w-xs p-4 pt-0">
				<strong class="block uppercase leading-normal text-sm">Date from</strong>
				<span class="block text-sm break-words">{{ $addressFilters->dateFrom }}</span>
			</div>
		@endif

		@if ($addressFilters->hasUsedFilter('date_to'))
			<div class="w-full md:w-auto md:min-w-xs p-4 pt-0">
				<strong class="block uppercase leading-normal text-sm">Date to</strong>
				<span class="block text-sm break-words">{{ $addressFilters->dateTo }}</span>
			</div>
		@endif

		@if ($addressFilters->hasUsedFilter('amount_from'))
			<div class="w-full md:w-auto md:min-w-xs p-4 pt-0">
				<strong class="block uppercase leading-normal text-sm">Amount from</strong>
				<span class="block text-sm break-words">{{ $addressFilters->amountFrom }}</span>
			</div>
		@endif

		@if ($addressFilters->hasUsedFilter('amount_to'))
			<div class="w-full md:w-auto md:min-w-xs p-4 pt-0">
				<strong class="block uppercase leading-normal text-sm">Amount to</strong>
				<span class="block text-sm break-words">{{ $addressFilters->amountTo }}</span>
			</div>
		@endif

		@if ($addressFilters->hasUsedFilter('directions'))
			<div class="w-full md:w-auto md:min-w-xs p-4 pt-0">
				<strong class="block uppercase leading-normal text-sm">Directions</strong>
				<span class="block text-sm break-words">{{ implode(', ', $addressFilters->directions) }}</span>
			</div>
		@endif

		@if ($showRemarkFilter && $addressFilters->hasUsedFilter('remark'))
			<div class="w-full md:w-auto md:min-w-xs p-4 pt-0">
				<strong class="block uppercase leading-normal text-sm">Remark</strong>
				<span class="block text-sm break-words">{{ $addressFilters->remark }}</span>
			</div>
		@endif
	</div>

	<div class="text-right float-right">
		<a href="/block/{{ $block->getProperties()->get('balance_address') }}#block-as-address" rel="nofollow" class="bg-transparent border border-blue text-blue hover:text-blue-dark hover:border-blue-dark text-sm font-medium py-2 px-4 rounded tracking-wide cursor-pointer inline-flex items-center justify-center">
			<span class="h-4 mr-2">
				@svg('x', 'fill-current')
			</span>

			<span>Clear filters</span>
		</a>
	</div>

	@php($filtered_earnings = $block->getFilteredEarnings())
	@php($filtered_spendings = $block->getFilteredSpendings())

	@if (bccomp($filtered_earnings, '0.000000000') > 0 || bccomp($filtered_spendings, '0.000000000') > 0)
		<strong class="text-lg block mb-4">Filtered totals</strong>
		@if (bccomp($filtered_earnings, '0.000000000') > 0)
			<span class="rounded bg-green uppercase px-3 py-1 text-xs mr-3 text-white text-center mx-auto" title="Total earnings in filtered data" v-tippy>+{{ $filtered_earnings }}</span>
		@endif
		@if (bccomp($filtered_spendings, '0.000000000') > 0)
			<span class="rounded bg-red uppercase px-3 py-1 text-xs mr-3 text-white text-center mx-auto" title="Total spendings in filtered data" v-tippy>-{{ $filtered_spendings }}</span>
		@endif
	@endif

	<div class="clearfix"></div>
</div>
