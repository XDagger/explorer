<h3>Block as address</h3>
<div>Filtered entries: <strong>{{ number_format($addressPagination->totalNumberOfItems(), 0) }}</strong></div>

<table id="block-as-address" cellpadding="10">
	<thead>
	<tr>
		<th>Direction</th>
		<th>Transaction</th>
		<th>Amount</th>
		<th>Date and Time (UTC)</th>
	</tr>
	</thead>

	<tbody>
	@php($show_totals = true)
	@php($earnings = $block->getEarningsSum())
	@php($spendings = $block->getSpendingsSum())
	@forelse ($block->getAddresses() as $address)
		<tr>
			<td>{{ $address['direction'] }}</td>

			<td>
				<a href="/text/block/{{ $address['address'] }}" rel="nofollow">
					{{ $address['address'] }}
				</a>
				@if ($address['remark'] !== '')
					<br><small>{{ $address['remark'] }}</small>
				@endif
			</td>

			<td>{{ $address['amount'] }}</td>

			<td>
				{{ $address['time'] }}
			</td>
		</tr>
	@empty
		<tr>
			<td colspan="4" align="center">There are no results.</td>
		</tr>
		@php($show_totals = false)
	@endforelse
	@if ($show_totals && (bccomp($earnings, '0.000000000') > 0 || bccomp($spendings, '0.000000000') > 0))
		<tr>
			<td>
				<strong>On page</strong>
			</td>
			<td colspan="3">
				@if (bccomp($earnings, '0.000000000') > 0)
					+{{ $earnings }} (earnings)
				@endif
				@if (bccomp($spendings, '0.000000000') > 0)
					-{{ $spendings }} (spendings)
				@endif
			</td>
		</tr>
	@endif
	</tbody>
</table>

<br>
@include('block.partials.text-pagination', ['pagination' => $addressPagination, 'table' => '#block-as-address'])
