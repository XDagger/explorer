<h3>Block as transaction</h3>
<div>Transactions: <strong>{{ number_format($transactionPagination->totalNumberOfItems(), 0) }}</strong></div>

<table id="block-as-transaction" cellpadding="10">
	<thead>
	<tr>
		<th>Direction</th>
		<th>Address</th>
		<th>Amount</th>
	</tr>
	</thead>

	<tbody>
	@php($inputs = $outputs = 0)
	@forelse ($block->getTransactions() as $transaction)
		<tr>
			<td>{{ $transaction['direction'] }}</td>

			@if (in_array($transaction['direction'], ['earning', 'input']))
				@php ($inputs = bcadd($inputs, $transaction['amount'], 9))
			@else
				@php ($outputs = bcadd($outputs, $transaction['amount'], 9))
			@endif

			<td>
				<a href="/text/block/{{ $transaction['address'] }}" rel="nofollow">
					{{ $transaction['address'] }}
				</a>
			</td>

			<td>{{ $transaction['amount'] }}</td>
		</tr>
	@empty
		<tr>
			<td colspan="3" align="center">There are no results.</td>
		</tr>
		@php($inputs = null)
	@endforelse
	@if ($inputs !== null && ($inputs > 0 || $outputs > 0))
		<tr>
			<td>
				<strong>Totals</strong>
			</td>
			<td colspan="3">
				@if ($inputs > 0)
					+{{ number_format($inputs, 9) }} (inputs)
				@endif
				@if ($outputs > 0)
					-{{ number_format($outputs, 9) }} (outputs)
				@endif
			</td>
		</tr>
	@endif
	</tbody>
</table>
<br>

@include('block.partials.text-pagination', ['pagination' => $transactionPagination, 'table' => '#block-as-transaction'])
