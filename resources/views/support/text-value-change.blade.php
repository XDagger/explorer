@php
$reverseArrows = $reverseArrows ?? false;
$type = $type ?? 'percentage';
$value = $type == 'percentage' ? $valueChange['percentageChange'] . '%' : number_format($valueChange['valueChange'], 9);
@endphp

@if ($valueChange['increased'])
		({{ $reverseArrows ? '' : '+' }}{{ $value }} increase {{ $change }})
@else
	@if ($valueChange['isSame'])
		(no change)
	@else
		({{ $reverseArrows ? '+' : '' }}{{ $value }} decrease {{ $change }})
	@endif
@endif
