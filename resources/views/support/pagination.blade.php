@if ($paginator->lastPage() > 1)
	<div class="w-full mt-8 -mb-2">
		<div class="flex flex-wrap items-start justify-between">
			<div class="flex-order-1 w-1/2 sm:w-auto pr-1 sm:pr-0">
				<a href="{{ $paginator->previousPageUrl() }}" rel="nofollow" class="flex items-center justify-center button secondary small {{ $paginator->onFirstPage() ? 'disabled' : '' }}" onclick="return {{ $paginator->onFirstPage() ? 'false' : 'true' }}">Previous</a>
			</div>

			<div class="flex-order-3 sm:flex-order-2 flex-1 text-center px-2 flex flex-wrap items-center justify-center mt-2 sm:mt-0">
				@foreach ($elements as $element)
					{{-- "Three Dots" Separator --}}
					@if (is_string($element))
						<div class="flex items-center justify-center button secondary small disabled mb-2 mr-2">{{ $element }}</div>
					@endif

					{{-- Array Of Links --}}
					@if (is_array($element))
						@foreach ($element as $page => $url)
							@if ($page == $paginator->currentPage())
								<a href="{{ $url }}" rel="nofollow" class="flex items-center justify-center button primary small mb-2 mr-2" aria-current="page">{{ $page }}</a>
							@else
								<a href="{{ $url }}" rel="nofollow" class="flex items-center justify-center button secondary small mb-2 mr-2" aria-current="page">{{ $page }}</a>
							@endif
						@endforeach
					@endif
				@endforeach
			</div>

			<div class="flex-order-2 sm:flex-order-3 w-1/2 sm:w-auto pl-1 sm:pl-0">
				<a href="{{ $paginator->nextPageUrl() }}" rel="nofollow" class="flex items-center justify-center button secondary small {{ $paginator->currentPage() == $paginator->lastPage() ? 'disabled' : '' }}" onclick="return {{ $paginator->currentPage() == $paginator->lastPage() ? 'false' : 'true' }}">Next</a>
			</div>
		</div>
	</div>
@endif
