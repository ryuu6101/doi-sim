@if ($paginator->hasPages())
<ul class="pagination pagination-flat align-self-center">

    @if ($paginator->onFirstPage())
        <li class="page-item disabled"><span class="page-link"><i class="icon-arrow-left12"></i></span></li>
    @else
        <li class="page-item"><a href="{{ $paginator->previousPageUrl() }}" class="page-link"><i class="icon-arrow-left12"></i></a></li>
    @endif

    @foreach ($elements as $element)

        @if (is_array($element))
            @foreach ($element as $page => $url)
                @if ($page == $paginator->currentPage())
                    <li class="page-item active"><span class="page-link">{{ $page }}</span></li>
                @elseif (($page == $paginator->currentPage() + 1 || $page == $paginator->currentPage() + 1) || $page == $paginator->lastPage())
                    <li class="page-item"><a class="page-link" href="{{ $url }}">{{ $page }}</a></li>
                @elseif (($page == $paginator->currentPage() - 1 || $page == $paginator->currentPage() - 1) || $page == $paginator->lastPage())
                    <li class="page-item"><a class="page-link" href="{{ $url }}">{{ $page }}</a></li>
                @elseif ($page == $paginator->lastPage() - 1)
                    <li class="page-item disabled" aria-disabled="true"><span class="page-link">{{ '...' }}</span></li>
                @elseif($page == 2 && $paginator->currentPage() >= 4)
                    <li class="page-item disabled" aria-disabled="true"><span class="page-link">{{ '...' }}</span></li>
                @elseif($page == 1 )
                    <li class="page-item"><a class="page-link" href="{{ $url }}">{{ $page }}</a></li>
                @endif
            @endforeach
        @endif

    @endforeach

    @if ($paginator->hasMorePages())
        <li class="page-item"><a class="page-link" href="{{ $paginator->nextPageUrl() }}"><i class="icon-arrow-right13"></i></a> </li>
    @else
        <li class="page-item disabled"><span class="page-link"><i class="icon-arrow-right13"></i></span></li>
    @endif
</ul>
@endif