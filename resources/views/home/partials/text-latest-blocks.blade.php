<h2>Latest blocks</h2>
<h3>Last 20 main blocks</h3>

@foreach ($last_blocks as $chunk)
	@foreach($chunk as $last_block)
		<span>({{ $last_block->found_at->format('Y-m-d H:i:s') }} UTC)</span>
		<a href="/text/block/{{ $last_block->address }}" rel="nofollow">{{ $last_block->address }}</a>

		@if (! $loop->last)
			<br>
			<br>
		@endif
	@endforeach

	@if (! $loop->last)
		<br>
		<br>
	@endif
@endforeach
