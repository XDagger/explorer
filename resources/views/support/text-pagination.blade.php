@if ($paginator->lastPage() > 1)
    <a href="{{ $paginator->previousPageUrl() }}" rel="nofollow">Previous</a>
    @foreach ($elements as $element)
        {{-- "Three Dots" Separator --}}
        @if (is_string($element))
            <div>{{ $element }}</div>
        @endif

        {{-- Array Of Links --}}
        @if (is_array($element))
            @foreach ($element as $page => $url)
                @if ($page == $paginator->currentPage())
                    <a href="{{ $url }}" rel="nofollow">{{ $page }}</a>
                @else
                    <a href="{{ $url }}" rel="nofollow">{{ $page }}</a>
                @endif
            @endforeach
        @endif
    @endforeach
    <a href="{{ $paginator->nextPageUrl() }}" rel="nofollow">Next</a>
@endif
