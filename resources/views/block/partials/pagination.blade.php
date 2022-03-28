@if ($pagination->lastPage() > 1)
	<div class="w-full mt-8 -mb-2">
		<div class="flex flex-wrap items-start justify-between">
			<div class="flex-order-1 w-1/2 sm:w-auto pr-1 sm:pr-0">
				<a href="{{ $pagination->prevPageLink().($table ?? '') }}" rel="nofollow" class="flex items-center justify-center button secondary small {{ $pagination->isFirstPage() ? 'disabled' : '' }}">Previous</a>
			</div>

			<div class="flex-order-3 sm:flex-order-2 flex-1 text-center px-2 flex flex-wrap items-center justify-center mt-2 sm:mt-0">
				@if (! $pagination->paginationLinksShowingFirst())
					<a href="{{ $pagination->firstPageLink().($table ?? '') }}" rel="nofollow" class="flex items-center justify-center button secondary small mb-2 mr-2">1</a>
					<div class="flex items-center justify-center button secondary small disabled mb-2 mr-2">...</div>
				@endif

				@for ($i = $pagination->paginationLinksStart(); $i <= $pagination->paginationLinksEnd(); $i++)
					<a href="{{ $pagination->pageLink($i).($table ?? '') }}" rel="nofollow" class="flex items-center justify-center button {{ $pagination->isPage($i) ? 'primary' : 'secondary' }} small mb-2 mr-2">{{ $i }}</a>
				@endfor

				@if (! $pagination->paginationLinksShowingEnd())
					<div class="flex items-center justify-center button secondary small disabled mb-2 mr-2">...</div>
					<a href="{{ $pagination->lastPageLink().($table ?? '') }}" rel="nofollow" class="flex items-center justify-center button secondary small mb-2 mr-2">{{ $pagination->lastPage() }}</a>
				@endif
			</div>

			<div class="flex-order-2 sm:flex-order-3 w-1/2 sm:w-auto pl-1 sm:pl-0">
				<a href="{{ $pagination->nextPageLink().($table ?? '') }}" rel="nofollow" class="flex items-center justify-center button secondary small {{ $pagination->isLastPage() ? 'disabled' : '' }}">Next</a>
			</div>
		</div>
	</div>
@endif
