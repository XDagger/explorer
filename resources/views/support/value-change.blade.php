@php
$reverseArrows = $reverseArrows ?? false;
$reverseColor = $reverseColor ?? false;
$type = $type ?? 'percentage';
$value = $type == 'percentage' ? abs($valueChange['percentageChange']) . '%' : number_format(abs($valueChange['valueChange']), 9);
@endphp

@if ($valueChange['increased'])
	<div class="flex items-center {{ $reverseColor ? 'text-red' : 'text-green' }} text-sm" title="{{ $name }} increased by {{ $value }} {{ $change }}" v-tippy>
		@if ($reverseArrows)
			@svg('arrow-down')
		@else
			@svg('arrow-up')
		@endif

		<span class="ml-2">{{ $value }}</span>
	</div>
@else
	@if ($valueChange['isSame'])
		<div class="flex items-center text-orange text-sm" title="{{ $name }} did not change {{ $change }}" v-tippy>
			<span class="ml-2">No change</span>
		</div>
	@else
		<div class="flex items-center {{ $reverseColor ? 'text-green' : 'text-red' }} text-sm" title="{{ $name }} decreased by {{ $value }} {{ $change }}" v-tippy>
			@if ($reverseArrows)
				@svg('arrow-up')
			@else
				@svg('arrow-down')
			@endif

			<span class="ml-2">{{ $value }}</span>
		</div>
	@endif
@endif
