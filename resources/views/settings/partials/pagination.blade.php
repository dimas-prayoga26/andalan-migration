@if ($items->total() > 0)
    <div class="settings-table-footer dataTables_wrapper no-footer">
        <div class="dataTables_info">
            Showing {{ $items->firstItem() }} to {{ $items->lastItem() }} of {{ $items->total() }} entries
        </div>
        <div class="dataTables_paginate paging_simple_numbers">
            <a
                class="paginate_button previous {{ $items->onFirstPage() ? 'disabled' : '' }}"
                href="{{ $items->previousPageUrl() ?? '#' }}"
                aria-label="Previous page"
            ><i class="fa-solid fa-angle-left"></i></a>
            <span>
                @foreach ($items->getUrlRange(1, $items->lastPage()) as $page => $url)
                    <a
                        class="paginate_button {{ $page === $items->currentPage() ? 'current' : '' }}"
                        href="{{ $url }}"
                        @if ($page === $items->currentPage()) aria-current="page" @endif
                    >{{ $page }}</a>
                @endforeach
            </span>
            <a
                class="paginate_button next {{ $items->hasMorePages() ? '' : 'disabled' }}"
                href="{{ $items->nextPageUrl() ?? '#' }}"
                aria-label="Next page"
            ><i class="fa-solid fa-angle-right"></i></a>
        </div>
    </div>
@endif
