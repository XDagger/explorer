<h2>Network information</h2>

<strong>Blocks</strong>
<span>{{ number_format($network->blocks, 0) }} ({{ number_format($new_blocks) }} new blocks in last minute)</span>
<br>
<br>

<strong>Main blocks</strong>
<span>{{ number_format($network->main_blocks, 0) }}</span>
<br>
<br>

<strong>Supply</strong>
<span>{{ number_format($network->supply, 0) }} XDAG</span>
<br>
<br>

<strong>Network hashrate</strong>
<span>{{ \App\Xdag\Hashpower::format($network->hashrate, 2) }} @include('support.text-value-change', ['valueChange' => $hashrate_change, 'name' => 'Network hashrate', 'change' => 'in last hour'])</span>
<br>
<br>

<strong>Difficulty</strong>
<span>{{ $network->difficulty }}</span>
<br>
<br>
