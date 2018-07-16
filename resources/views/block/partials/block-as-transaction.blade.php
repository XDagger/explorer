<div class="box" id="block-as-transaction">
	<tabs inline-template :default="{{ request()->has('transaction-filter') ? 1 : 0 }}">
		<div>
			<div class="flex flex-wrap items-start justify-between mb-8">
				<div class="w-full md:w-1/2">
					<h4 class="box-title">Block as transaction</h4>
					<div class="box-sub-title mb-0">Transactions: <strong>{{ number_format($transactionPagination->totalNumberOfItems(), 0) }}</strong></div>
				</div>

				<modal inline-template :shown="{{ $transactionFiltersValidation->errors()->any() ? 'true' : 'false' }}">
					<div class="w-full md:w-auto mt-4 md:mt-0">
						<div class="w-full flex items-center md:justify-end text-blue-dark font-medium cursor-pointer tracking-wide text-center" @click="toggleModal">
							<div class="h-4 mr-2">
								@svg('filter', 'stroke-current')
							</div>

							<span>Filter</span>
						</div>

						@include('block.partials.block-as-transaction-filter')
					</div>
				</modal>
			</div>
		</div>
	</tabs>

	@if ($transactionFilters->isUsed())
		@include('block.partials.block-as-transaction-used-filters')
	@endif

	<div class="w-full overflow-auto">
		<table class="w-full">
			<thead>
			<tr>
				<th class="border-b border-grey-lighter p-3 text-center text-black font-bold w-40">Direction</th>
				<th class="border-b border-grey-lighter p-3 text-left text-black font-bold">Address</th>
				<th class="border-b border-grey-lighter p-3 text-right">Amount</th>
			</tr>
			</thead>

			<tbody>
			@php($inputs = $outputs = 0)
			@forelse ($block->getTransactions() as $transaction)
				<tr>
					<td class="p-3 {{ $loop->index % 2 ? 'bg-grey-lightest' : 'bg-white' }}">
						@if ($transaction['direction'] == 'fee')
							<span class="rounded bg-grey-darker uppercase px-3 py-1 text-xs font-bold mr-3 text-white block w-auto sm:w-2/3 text-center mx-auto">Fee</span>
							@php ($outputs = bcadd($outputs, $transaction['amount'], 9))
						@elseif ($transaction['direction'] == 'input')
							<span class="rounded bg-green uppercase px-3 py-1 text-xs font-bold mr-3 text-white block w-auto sm:w-2/3 text-center mx-auto">Input</span>
							@php ($inputs = bcadd($inputs, $transaction['amount'], 9))
						@elseif ($transaction['direction'] == 'output')
							<span class="rounded bg-orange uppercase px-3 py-1 text-xs font-bold mr-3 text-white block w-auto sm:w-2/3 text-center mx-auto">Output</span>
							@php ($outputs = bcadd($outputs, $transaction['amount'], 9))
						@elseif ($transaction['direction'] == 'earning')
							<span class="rounded bg-yellow uppercase px-3 py-1 text-xs font-bold mr-3 text-black block w-auto sm:w-2/3 text-center mx-auto">Earning</span>
							@php ($inputs = bcadd($inputs, $transaction['amount'], 9))
						@endif
					</td>

					<td class="p-3 {{ $loop->index % 2 ? 'bg-grey-lightest' : 'bg-white' }}">
						<a href="/block/{{ $transaction['address'] }}" class="leading-normal text-sm" rel="nofollow">
							{{ $transaction['address'] }}
						</a>
					</td>

					<td class="p-3 {{ $loop->index % 2 ? 'bg-grey-lightest' : 'bg-white' }} text-right">{{ $transaction['amount'] }}</td>
				</tr>
			@empty
				<tr>
					<td colspan="3" class="p-3">
						<div class="flex items-center justify-center w-full text-grey">
							<span class="w-4 h-4 mr-2">
								@svg('info', 'stroke-current')
							</span>

							<span>There are no results.</span>
						</div>
					</td>
				</tr>
				@php($inputs = null)
			@endforelse
			@if ($inputs !== null && ($inputs > 0 || $outputs > 0))
				<tr>
					<td class="p-3 bg-white text-left">
						<span class="rounded bg-purple uppercase px-3 py-1 text-xs font-bold mr-3 text-white block w-auto sm:w-2/3 text-center mx-auto">Totals</span>
					</td>
					<td colspan="3" class="p-3 bg-white text-left">
						@if ($inputs > 0)
							<span class="rounded bg-green uppercase px-3 py-1 text-xs mr-3 text-white text-center mx-auto" title="Total inputs on this page" v-tippy>+{{ number_format($inputs, 9) }}</span>
						@endif
						@if ($outputs > 0)
							<span class="rounded bg-red uppercase px-3 py-1 text-xs mr-3 text-white text-center mx-auto" title="Total outputs on this page" v-tippy>-{{ number_format($outputs, 9) }}</span>
						@endif
					</td>
				</tr>
			@endif
			</tbody>
		</table>
	</div>

	@include('block.partials.pagination', ['pagination' => $transactionPagination, 'table' => '#block-as-transaction'])
</div>
