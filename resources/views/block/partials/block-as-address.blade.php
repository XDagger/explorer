@php($entries = $walletListing->get())
@php($filters = $walletListing->usedFilters())
@php($errors = $walletListing->errors())

<div class="box" id="block-as-address">
	<div class="flex flex-wrap items-start justify-between mb-8">
		<div class="w-full md:w-1/2">
			<h4 class="box-title">Block as address</h4>
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

				@include('block.partials.block-as-address-filter')
			</div>
		</modal>
	</div>

	@if ($walletListing->isFiltered())
		@include('block.partials.block-as-address-used-filters')
	@endif

	<div class="w-full overflow-auto">
		<table class="w-full">
			<thead>
				<tr>
					<th class="border-b border-grey-lighter p-3 text-center text-black font-bold w-40">Direction</th>
					<th class="border-b border-grey-lighter p-3 text-left text-black font-bold">Transaction</th>
					<th class="border-b border-grey-lighter p-3 text-center">Amount</th>
					<th class="border-b border-grey-lighter p-3 text-right">Date and Time (UTC)</th>
				</tr>
			</thead>

			<tbody>
				@php($showTotals = true)
				@php($earnings = '0.000000000')
				@php($spendings = '0.000000000')
				@forelse ($entries as $entry)
					<tr>
						<td class="p-3 {{ $loop->index % 2 ? 'bg-grey-lightest' : 'bg-white' }}">
							@if ($entry->direction == 'input')
								@php($earnings = bcadd($earnings, $entry->amount, 9))
								<span class="rounded bg-green uppercase px-3 py-1 text-xs font-bold mr-3 text-white block w-auto sm:w-2/3 text-center mx-auto">Input</span>
							@elseif ($entry->direction == 'output')
								@php($spendings = bcadd($spendings, ltrim($entry->amount, '-'), 9))
								<span class="rounded bg-orange uppercase px-3 py-1 text-xs font-bold mr-3 text-white block w-auto sm:w-2/3 text-center mx-auto">Output</span>
							@elseif ($entry->direction == 'earning')
								@php($earnings = bcadd($earnings, $entry->amount, 9))
								<span class="rounded bg-yellow uppercase px-3 py-1 text-xs font-bold mr-3 text-black block w-auto sm:w-2/3 text-center mx-auto">Earning</span>
							@elseif ($entry->direction == 'snapshot')
								@php($earnings = bcadd($earnings, $entry->amount, 9))
								<span class="rounded bg-smoke uppercase px-3 py-1 text-xs font-bold mr-3 text-white block w-auto sm:w-2/3 text-center mx-auto">Snapshot</span>
							@endif
						</td>

						<td class="p-3 {{ $loop->index % 2 ? 'bg-grey-lightest' : 'bg-white' }}">
							<a href="/block/{{ $entry->address }}" class="leading-normal text-sm" rel="nofollow">
								{{ $entry->address }}
							</a>
							@if ($entry->remark !== null)
								<br><span class="text-sm text-grey-darker">{!! clickableFullLinks($entry->remark) !!}</span>
							@endif
						</td>

						<td class="p-3 {{ $loop->index % 2 ? 'bg-grey-lightest' : 'bg-white' }} text-center">{{ number_format(ltrim($entry->amount, '-'), 9) }}</td>

						<td class="p-3 {{ $loop->index % 2 ? 'bg-grey-lightest' : 'bg-white' }} text-right">{{ $entry->created_at->format('Y-m-d H:i:s.v') }}</td>
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
					@php($showTotals = false)
				@endforelse
				@if ($showTotals && (bccomp($earnings, '0.000000000') > 0 || bccomp($spendings, '0.000000000') > 0))
					<tr>
						<td class="p-3 bg-white text-left">
							<span class="rounded bg-purple uppercase px-3 py-1 text-xs font-bold mr-3 text-white block w-auto sm:w-2/3 text-center mx-auto">On page</span>
						</td>
						<td colspan="3" class="p-3 bg-white text-left">
							@if (bccomp($earnings, '0.000000000') > 0)
								<span class="rounded bg-green uppercase px-3 py-1 text-xs mr-3 text-white text-center mx-auto" title="Total earnings on this page" v-tippy>+{{ number_format($earnings, 9) }}</span>
							@endif
							@if (bccomp($spendings, '0.000000000') > 0)
								<span class="rounded bg-red uppercase px-3 py-1 text-xs mr-3 text-white text-center mx-auto" title="Total spendings on this page" v-tippy>-{{ number_format($spendings, 9) }}</span>
							@endif
						</td>
					</tr>
				@endif
			</tbody>
		</table>
	</div>

	{{ $entries->fragment('block-as-address')->links('support.pagination') }}
</div>
