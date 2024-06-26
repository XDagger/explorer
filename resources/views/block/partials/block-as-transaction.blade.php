@php($entries = $transactionsListing->get())
@php($filters = $transactionsListing->usedFilters())
@php($errors = $transactionsListing->errors())

<div class="box mb-8" id="block-as-transaction">
	<div class="flex flex-wrap items-start justify-between mb-8">
		<div class="w-full md:w-1/2">
			<h4 class="box-title">Block as transaction</h4>
			<div class="box-sub-title mb-0">Filtered entries: <strong>{{ number_format($entries->total(), 0) }}</strong></div>
		</div>

		<modal inline-template :shown="{{ count($errors) ? 'true' : 'false' }}">
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

	@if ($transactionsListing->isFiltered())
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
				@php($showTotals = true)
				@php($fees = '0.000000000')
				@php($inputs = '0.000000000')
				@php($outputs = '0.000000000')
				@forelse ($entries as $entry)
					<tr>
						<td class="p-3 {{ $loop->index % 2 ? 'bg-grey-lightest' : 'bg-white' }}">
							@if ($entry->direction == 'fee')
								@php($fees = bcadd($fees, ltrim($entry->amount, '-'), 9))
								<span class="rounded bg-grey-darker uppercase px-3 py-1 text-xs font-bold mr-3 text-white block w-auto sm:w-2/3 text-center mx-auto">Fee</span>
							@elseif ($entry->direction == 'input')
								@php($inputs = bcadd($inputs, $entry->amount, 9))
								<span class="rounded bg-green uppercase px-3 py-1 text-xs font-bold mr-3 text-white block w-auto sm:w-2/3 text-center mx-auto">Input</span>
							@elseif ($entry->direction == 'output')
								@php($outputs = bcadd($outputs, ltrim($entry->amount, '-'), 9))
								<span class="rounded bg-orange uppercase px-3 py-1 text-xs font-bold mr-3 text-white block w-auto sm:w-2/3 text-center mx-auto">Output</span>
							@endif
						</td>

						<td class="p-3 {{ $loop->index % 2 ? 'bg-grey-lightest' : 'bg-white' }}">
							<a href="/block/{{ $entry->address }}" class="leading-normal text-sm" rel="nofollow">
								{{ $entry->address }}
							</a>
						</td>

						<td class="p-3 {{ $loop->index % 2 ? 'bg-grey-lightest' : 'bg-white' }} text-right">{{ number_format(ltrim($entry->amount, '-'), 9) }}</td>
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
					@php($showTotals = false)
				@endforelse

				@if ($showTotals && (bccomp($fees, '0.000000000', 9) > 0 || bccomp($inputs, '0.000000000', 9) > 0 || bccomp($outputs, '0.000000000', 9) > 0))
					<tr>
						<td class="p-3 bg-white text-left">
							<span class="rounded bg-purple uppercase px-3 py-1 text-xs font-bold mr-3 text-white block w-auto sm:w-2/3 text-center mx-auto whitespace-pre">On page</span>
						</td>
						<td colspan="2" class="p-3 bg-white text-left">
							@if (bccomp($inputs, '0.000000000', 9) > 0)
								<span class="rounded bg-green uppercase px-3 py-1 text-xs mr-3 text-white text-center mx-auto" title="Total inputs on this page" v-tippy>+{{ number_format($inputs, 9) }}</span>
							@endif
							@if (bccomp($outputs, '0.000000000', 9) > 0)
								<span class="rounded bg-red uppercase px-3 py-1 text-xs mr-3 text-white text-center mx-auto" title="Total outputs on this page" v-tippy>-{{ number_format($outputs, 9) }}</span>
							@endif
							@if (bccomp($fees, '0.000000000', 9) > 0)
								<span class="rounded bg-grey-darker uppercase px-3 py-1 text-xs mr-3 text-white text-center mx-auto" title="Total fees on this page" v-tippy>{{ number_format($fees, 9) }}</span>
							@endif
						</td>
					</tr>
				@endif
			</tbody>
		</table>
	</div>

	{{ $entries->fragment('block-as-transaction')->links('support.pagination') }}
</div>
