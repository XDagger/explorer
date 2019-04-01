<h3>Block as transaction</h3>
<div>Filtered entries: <strong>{{ number_format($transactionPagination->totalNumberOfItems(), 0) }}</strong></div>

<table id="block-as-transaction" cellpadding="10">
	<thead>
	<tr>
		<th>Direction</th>
		<th>Address</th>
		<th>Amount</th>
	</tr>
	</thead>

	<tbody>
	@php($show_totals = true)
	@php($fees = $block->getFeesSum())
	@php($inputs = $block->getInputsSum())
	@php($outputs = $block->getOutputsSum())
	@forelse ($block->getTransactions() as $transaction)
		<tr>
			<td>{{ $transaction['direction'] }}</td>

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
		@php($show_totals = false)
	@endforelse
	@if ($show_totals && (bccomp($fees, '0.000000000') > 0 || bccomp($inputs, '0.000000000') > 0 || bccomp($outputs, '0.000000000') > 0))
		<tr>
			<td>
				<strong>On page</strong>
			</td>
			<td colspan="2">
				@if (bccomp($inputs, '0.000000000') > 0)
					+{{ $inputs }} (inputs)
				@endif
				@if (bccomp($outputs, '0.000000000') > 0)
					-{{ $outputs }} (outputs)
				@endif
				@if (bccomp($fees, '0.000000000') > 0)
					-{{ $fees }} (fees)
				@endif
			</td>
		</tr>
	@endif
	</tbody>
</table>
<br>

@include('block.partials.text-pagination', ['pagination' => $transactionPagination, 'table' => '#block-as-transaction'])
