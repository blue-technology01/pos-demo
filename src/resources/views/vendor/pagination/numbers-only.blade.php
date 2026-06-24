@if ($paginator->hasPages())
    <div class="pagination-numbers" onclick="showLoader()">
        {{-- Page Numbers --}}
        @foreach ($elements as $element)
            {{-- Dots --}}
            @if (is_string($element))
                <span class="pagination-dots">
                    {{ $element }}
                </span>
            @endif
            {{-- Links --}}
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="pagination-active">
                            {{ $page }}
                        </span>
                    @else
                        <a href="{{ $url }}">
                            {{ $page }}
                        </a>
                    @endif
                @endforeach
            @endif
        @endforeach
    </div>
@endif
