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

	<div class="text-right">
		<a href="{{ url()->current() }}#block-as-transaction" rel="nofollow" class="bg-transparent border border-blue text-blue hover:text-blue-dark hover:border-blue-dark text-sm font-medium py-2 px-4 rounded tracking-wide cursor-pointer inline-flex items-center justify-center">
			<span class="h-4 mr-2">
				@svg('x', 'fill-current')
			</span>

			<span>Clear filters</span>
		</a>
	</div>
</div>
