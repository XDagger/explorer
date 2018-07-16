<table cellpadding="10">
	<tr>
		@if ($pagination->lastPage() > 1)
			@if (! $pagination->isFirstPage())
				<td>
					<a href="{{ $pagination->prevPageLink().($table ?? '') }}" rel="nofollow">Previous</a>
				</td>
			@endif

			@if (! $pagination->paginationLinksShowingFirst())
				<td>
					<a href="{{ $pagination->firstPageLink().($table ?? '') }}" rel="nofollow">1 ...</a>
				</td>
			@endif

			@for ($i = $pagination->paginationLinksStart(); $i <= $pagination->paginationLinksEnd(); $i++)
				<td>
					<a href="{{ $pagination->pageLink($i).($table ?? '') }}" rel="nofollow">
						@if ($pagination->isPage($i))
							<strong>{{ $i }}</strong>
						@else
							{{ $i }}
						@endif
					</a>
				</td>
			@endfor

			@if (! $pagination->paginationLinksShowingEnd())
				<td>
					<a href="{{ $pagination->lastPageLink().($table ?? '') }}" rel="nofollow">... {{ $pagination->lastPage() }}</a>
				</td>
			@endif

			@if (! $pagination->isLastPage())
				<td>
					<a href="{{ $pagination->nextPageLink().($table ?? '') }}" rel="nofollow">Next</a>
				</td>
			@endif
		@endif
	</tr>
</table>
