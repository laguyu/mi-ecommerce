@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination Navigation">
        <div>
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <span aria-disabled="true">
                    <span>&laquo; Anterior</span>
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev">
                    &laquo; Anterior
                </a>
            @endif

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next">
                    Siguiente &raquo;
                </a>
            @else
                <span aria-disabled="true">
                    <span>Siguiente &raquo;</span>
                </span>
            @endif
        </div>
    </nav>
@endif
