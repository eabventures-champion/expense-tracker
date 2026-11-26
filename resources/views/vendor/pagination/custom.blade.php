@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination Navigation" class="pagination-nav">
        <div class="pagination-summary">
            Showing
            <span class="mono" style="font-weight: 700; color: var(--text-dark);">{{ $paginator->firstItem() ?? 0 }}</span>
            to
            <span class="mono" style="font-weight: 700; color: var(--text-dark);">{{ $paginator->lastItem() ?? 0 }}</span>
            of
            <span class="mono" style="font-weight: 700; color: var(--text-dark);">{{ $paginator->total() }}</span>
            records
        </div>

        <div class="pagination-list">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <span class="page-btn disabled" aria-disabled="true" aria-label="@lang('pagination.previous')">
                    ← Prev
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="page-btn" aria-label="@lang('pagination.previous')">
                    ← Prev
                </a>
            @endif

            {{-- Pagination Elements --}}
            @foreach ($elements as $element)
                {{-- "Three Dots" Separator --}}
                @if (is_string($element))
                    <span class="page-btn disabled" aria-disabled="true">{{ $element }}</span>
                @endif

                {{-- Array Of Links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="page-btn active mono" aria-current="page">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" class="page-btn mono" aria-label="{{ __('Go to page :page', ['page' => $page]) }}">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="page-btn" aria-label="@lang('pagination.next')">
                    Next →
                </a>
            @else
                <span class="page-btn disabled" aria-disabled="true" aria-label="@lang('pagination.next')">
                    Next →
                </span>
            @endif
        </div>
    </nav>
@endif
