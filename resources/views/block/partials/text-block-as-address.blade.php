<h3>Block as address</h3>
<div>Transactions: <strong>{{ number_format($addressPagination->totalNumberOfItems(), 0) }}</strong></div>

<table id="block-as-address" cellpadding="10">
	<thead>
	<tr>
		<th>Direction</th>
		<th>Transaction</th>
		<th>Amount</th>
		<th>Date and Time</th>
	</tr>
	</thead>

	<tbody>
	@php($earnings = $spendings = 0)
	@forelse ($block->getAddresses() as $address)
		<tr>
			<td>{{ $address['direction'] }}</td>

			@if (in_array($address['direction'], ['earning', 'input']))
				@php ($earnings = bcadd($earnings, $address['amount'], 9))
			@else
				@php ($spendings = bcadd($spendings, $address['amount'], 9))
			@endif

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
		@php($earnings = null)
	@endforelse
	@if ($earnings !== null && ($earnings > 0 || $spendings > 0))
		<tr>
			<td>
				<strong>Totals</strong>
			</td>
			<td colspan="3">
				@if ($earnings > 0)
					+{{ number_format($earnings, 9) }} (earnings)
				@endif
				@if ($spendings > 0)
					-{{ number_format($spendings, 9) }} (spendings)
				@endif
			</td>
		</tr>
	@endif
	</tbody>
</table>

<br>
@include('block.partials.text-pagination', ['pagination' => $addressPagination, 'table' => '#block-as-address'])
