<div class="box" id="block-as-address">
	<div>
		<div class="flex flex-wrap items-start justify-between mb-8">
			<div class="w-full md:w-1/2">
				<h4 class="box-title">Block as address</h4>
				<div class="box-sub-title mb-0">Transactions: <strong>{{ number_format($addressPagination->totalNumberOfItems(), 0) }}</strong></div>
			</div>

			<modal inline-template :shown="{{ $addressFiltersValidation->errors()->any() ? 'true' : 'false' }}">
				<div class="w-full md:w-auto mt-4 md:mt-0">
					<div class="w-full flex items-center md:justify-end text-blue-dark font-medium cursor-pointer tracking-wide text-center" @click="toggleModal">
						<div class="h-4 mr-2">
							@svg('filter', 'stroke-current')
						</div>

						<span>Filter</span>
					</div>

					@include('block.partials.block-as-address-filter')
				</div>
			</modal>
		</div>
	</div>

	@if ($addressFilters->isUsed())
		@include('block.partials.block-as-address-used-filters')
	@endif

	<div class="w-full overflow-auto">
		<table class="w-full">
			<thead>
			<tr>
				<th class="border-b border-grey-lighter p-3 text-center text-black font-bold w-40">Direction</th>
				<th class="border-b border-grey-lighter p-3 text-left text-black font-bold">Transaction</th>
				<th class="border-b border-grey-lighter p-3 text-center">Amount</th>
				<th class="border-b border-grey-lighter p-3 text-right">Date and Time</th>
			</tr>
			</thead>

			<tbody>
			@php($earnings = $spendings = 0)
			@forelse ($block->getAddresses() as $address)
				<tr>
					<td class="p-3 {{ $loop->index % 2 ? 'bg-grey-lightest' : 'bg-white' }}">

						@if ($address['direction'] == 'fee')
							<span class="rounded bg-grey-darker uppercase px-3 py-1 text-xs font-bold mr-3 text-white block w-auto sm:w-2/3 text-center mx-auto">Fee</span>
							@php ($spendings = bcadd($spendings, $address['amount'], 9))
						@elseif ($address['direction'] == 'input')
							<span class="rounded bg-green uppercase px-3 py-1 text-xs font-bold mr-3 text-white block w-auto sm:w-2/3 text-center mx-auto">Input</span>
							@php ($earnings = bcadd($earnings, $address['amount'], 9))
						@elseif ($address['direction'] == 'output')
							<span class="rounded bg-orange uppercase px-3 py-1 text-xs font-bold mr-3 text-white block w-auto sm:w-2/3 text-center mx-auto">Output</span>
							@php ($spendings = bcadd($spendings, $address['amount'], 9))
						@elseif ($address['direction'] == 'earning')
							<span class="rounded bg-yellow uppercase px-3 py-1 text-xs font-bold mr-3 text-black block w-auto sm:w-2/3 text-center mx-auto">Earning</span>
							@php ($earnings = bcadd($earnings, $address['amount'], 9))
						@endif
					</td>

					<td class="p-3 {{ $loop->index % 2 ? 'bg-grey-lightest' : 'bg-white' }}">
						<a href="/block/{{ $address['address'] }}" class="leading-normal text-sm" rel="nofollow">
							{{ $address['address'] }}
						</a>
						@if ($address['remark'] !== '')
							<br><span class="text-sm text-grey-darker">{{ $address['remark'] }}</span>
						@endif
					</td>

					<td class="p-3 {{ $loop->index % 2 ? 'bg-grey-lightest' : 'bg-white' }} text-center">{{ $address['amount'] }}</td>

					<td class="p-3 {{ $loop->index % 2 ? 'bg-grey-lightest' : 'bg-white' }} text-right">
						{{ $address['time'] }}
					</td>
				</tr>
			@empty
				<tr>
					<td colspan="4" class="p-3">
						<div class="flex items-center justify-center w-full text-grey">
							<span class="w-4 h-4 mr-2">
								@svg('info', 'stroke-current')
							</span>

							<span>There are no results.</span>
						</div>
					</td>
				</tr>
				@php($earnings = null)
			@endforelse
			@if ($earnings !== null && ($earnings > 0 || $spendings > 0))
				<tr>
					<td class="p-3 bg-white text-left">
						<span class="rounded bg-purple uppercase px-3 py-1 text-xs font-bold mr-3 text-white block w-auto sm:w-2/3 text-center mx-auto">Totals</span>
					</td>
					<td colspan="3" class="p-3 bg-white text-left">
						@if ($earnings > 0)
							<span class="rounded bg-green uppercase px-3 py-1 text-xs mr-3 text-white text-center mx-auto" title="Total earnings on this page" v-tippy>+{{ number_format($earnings, 9) }}</span>
						@endif
						@if ($spendings > 0)
							<span class="rounded bg-red uppercase px-3 py-1 text-xs mr-3 text-white text-center mx-auto" title="Total spendings on this page" v-tippy>-{{ number_format($spendings, 9) }}</span>
						@endif
					</td>
				</tr>
			@endif
			</tbody>
		</table>
	</div>

	@include('block.partials.pagination', ['pagination' => $addressPagination, 'table' => '#block-as-address'])
</div>
