<div class="box">
	<h2 class="box-title">Latest blocks</h2>
	<div class="box-sub-title">Last 20 main blocks</div>

	<div class="flex flex-wrap w-full">
		@foreach ($last_blocks as $chunk)
			<div class="w-full md:w-1/2">
				@foreach($chunk as $last_block)
					<div class="mb-4 {{ $loop->last ? 'sm:mb-0' : '' }} sm:flex items-center">
						<div class="mb-2 sm:mb-0 w-full sm:w-1/3 block text-center rounded-full bg-grey-lighter uppercase px-2 py-1 text-xs font-medium mr-3 text-grey-dark"{!! $last_block->remark !== null ? ' style="background-color: ' . color($last_block->remark) . '" title="' . ($last_block->height ? 'Height ' . $last_block->height . '&lt;br&gt;' : '') . 'Found by ' . e(e(links_to_domains($last_block->remark))) . '" v-tippy' : ($last_block->height ? ' title="Height ' . $last_block->height . '" v-tippy' : '') !!}>
							@if (($remark_link = first_link($last_block->remark)) !== null)
								<a href="{{ $remark_link }}" target="_blank" style="color: inherit">{{ $last_block->found_at->format('Y-m-d H:i:s') }} UTC</a>
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
