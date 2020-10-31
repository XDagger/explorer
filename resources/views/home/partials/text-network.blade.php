<h2>Network information</h2>

<strong>Blocks</strong>
<span>{{ number_format($network->blocks) }} ({{ number_format($new_blocks) }} new blocks in last minute)</span>
<br>
<br>

<strong>Main blocks</strong>
<a href="/text/block/{{ $network->main_blocks }}" title="Show latest main block">{{ number_format($network->main_blocks) }}</a>
<br>
<br>

<strong>Supply</strong>
<span>{{ number_format($network->supply) }} XDAG</span>
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
