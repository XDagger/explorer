<div class="box">
	<h2 class="box-title">Latest blocks</h2>
	<div class="box-sub-title">Last 20 main blocks</div>

	<div class="flex flex-wrap w-full">
		@foreach ($last_blocks as $chunk)
			<div class="w-full md:w-1/2">
				@foreach($chunk as $last_block)
					<div class="mb-4 {{ $loop->last ? 'sm:mb-0' : '' }} sm:flex items-center">
						<div class="mb-2 sm:mb-0 w-full sm:w-1/3 block text-center rounded-full bg-grey-lighter uppercase px-2 py-1 text-xs font-medium mr-3 text-grey-dark"{!! $last_block->remark !== null ? ' style="background-color: ' . $last_block->remark_background_color . '" title="Found by ' . e($last_block->clean_remark) . '" v-tippy' : '' !!}>
							@if ($last_block->first_remark_link !== null)
								<a href="{{ $last_block->first_remark_link }}" target="_blank" style="color: inherit">{{ $last_block->found_at->format('Y-m-d H:i:s') }} UTC</a>
							@else
								{{ $last_block->found_at->format('Y-m-d H:i:s') }} UTC
							@endif
						</div>

						<div class="w-full sm:w-2/3 sm:mr-2">
							<a href="/block/{{ $last_block->address }}" rel="nofollow" class="break-words break-all leading-normal text-sm">
								{{ $last_block->address }}
							</a>
						</div>
					</div>
				@endforeach
			</div>
		@endforeach
	</div>
</div>
