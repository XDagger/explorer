<div>
	<h2>Block Information</h2>

	<strong>Date and time</strong>
	<span>{{ $block->getProperties()->get('time') }} UTC</span>
	<br>
	<br>

	<strong>State</strong>
	<span>{{ ucfirst($block->getProperties()->get('state')) }}</span>
	<br>
	<br>

	<strong>Hash</strong>
	<a href="{{ route('block', ['address_or_hash' => $block->getProperties()->get('hash')]) }}" rel="nofollow">{{ $block->getProperties()->get('hash') }}</a>
	<br>
	<br>

	<strong>Address</strong>
	<a href="/text/block/{{ $block->getProperties()->get('balance_address') }}" rel="nofollow">{{ $block->getProperties()->get('balance_address') }}</a>
	<br>
	<br>

	@if ((string) $block->getProperties()->get('remark') !== '')
		<strong>Remark</strong>
		<span>{{ $block->getProperties()->get('remark') }}</span>
		<br>
		<br>
	@endif

	<strong>Difficulty</strong>
	<span>{{ $block->getProperties()->get('difficulty') }}</span>
	<br>
	<br>

	<strong>Kind</strong>
	<span>{{ $block->isMainBlock() ? 'Main block' : ($block->isTransactionBlock() ? 'Transaction block' : 'Wallet') }}</span>
	<br>
	<br>

	<strong>Timestamp</strong>
	<span>{{ $block->getProperties()->get('timestamp') }}</span>
	<br>
	<br>

	<strong>Flags</strong>
	<span>{{ $block->getProperties()->get('flags') }}</span>
	<br>
	<br>

	<strong>File pos</strong>
	<span>{{ $block->getProperties()->get('file_pos') }}</span>
	<br>
	<br>

	@if ($block->isTransactionBlock())
		<h3>Summary</h3>

		<strong>Total fee</strong>
		<span>{{ number_format($block->getTotalFees(), 9) }}</span>
		<br>
		<br>

		<strong>{{ $count = $block->getTotalInputsCount() }} input{{ $count !== 1 ? 's' : '' }}</strong>
		<span>total {{ number_format($block->getTotalInputs(), 9) }}</span>
		<br>
		<br>

		<strong>{{ $count = $block->getTotalOutputsCount() }} output{{ $count !== 1 ? 's' : '' }}</strong>
		<span>total {{ number_format($block->getTotalOutputs(), 9) }}</span>
	@else
		<h3>Balances</h3>

		<strong>Balance</strong>
		<span>{{ number_format($block->getBalance(), 9) }} @include('support.text-value-change', ['valueChange' => $balanceChange, 'name' => 'Balance', 'change' => 'since 24 hours ago', 'type' => 'value'])</span>
		<br>
		<br>

		<strong>Total earnings</strong>
		<span>{{ number_format($block->getTotalEarnings(), 9) }} @include('support.text-value-change', ['valueChange' => $earningChange, 'name' => 'Earnings', 'change' => 'since 24 hours ago', 'type' => 'value'])</span>
		<br>
		<br>

		<strong>Total spendings</strong>
		<span>{{ number_format($block->getTotalSpendings(), 9) }} @include('support.text-value-change', ['valueChange' => $spendingChange, 'name' => 'Spendings', 'change' => 'since 24 hours ago', 'type' => 'value'])</span>
	@endif
	<br>
	<br>
</div>
