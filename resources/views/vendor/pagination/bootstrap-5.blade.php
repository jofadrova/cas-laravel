@if ($paginator->hasPages())
<nav class="d-flex justify-content-between align-items-center flex-wrap">

    {{-- Versión móvil --}}
    <div class="d-flex justify-content-between flex-fill d-sm-none mb-2">
        <ul class="pagination mb-0">

            {{-- Anterior --}}
            @if ($paginator->onFirstPage())
                <li class="page-item disabled">
                    <span class="page-link">
                        <i class="fas fa-chevron-left"></i>
                    </span>
                </li>
            @else
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->previousPageUrl() }}">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                </li>
            @endif

            {{-- Siguiente --}}
            @if ($paginator->hasMorePages())
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->nextPageUrl() }}">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                </li>
            @else
                <li class="page-item disabled">
                    <span class="page-link">
                        <i class="fas fa-chevron-right"></i>
                    </span>
                </li>
            @endif

        </ul>
    </div>

    {{-- Escritorio --}}
    <div class="d-none d-sm-flex justify-content-between align-items-center w-100">

        {{-- Información --}}
        <div class="text-muted small">

            <strong>Registros:</strong>

            {{ $paginator->firstItem() }}

            -

            {{ $paginator->lastItem() }}

            de

            <strong>{{ $paginator->total() }}</strong>

            &nbsp;&nbsp;|&nbsp;&nbsp;

            <strong>Página:</strong>

            {{ $paginator->currentPage() }}

            de

            {{ $paginator->lastPage() }}

        </div>

        {{-- Navegación --}}
        <div>

            <ul class="pagination mb-0">

                {{-- Anterior --}}
                @if ($paginator->onFirstPage())
                    <li class="page-item disabled">
                        <span class="page-link">
                            <i class="fas fa-chevron-left"></i>
                        </span>
                    </li>
                @else
                    <li class="page-item">
                        <a class="page-link"
                           href="{{ $paginator->previousPageUrl() }}">

                            <i class="fas fa-chevron-left"></i>

                        </a>
                    </li>
                @endif

                {{-- Números --}}
                @foreach ($elements as $element)

                    @if (is_string($element))

                        <li class="page-item disabled">

                            <span class="page-link">

                                {{ $element }}

                            </span>

                        </li>

                    @endif

                    @if (is_array($element))

                        @foreach ($element as $page => $url)

                            @if ($page == $paginator->currentPage())

                                <li class="page-item active">

                                    <span class="page-link">

                                        {{ $page }}

                                    </span>

                                </li>

                            @else

                                <li class="page-item">

                                    <a class="page-link"

                                       href="{{ $url }}">

                                        {{ $page }}

                                    </a>

                                </li>

                            @endif

                        @endforeach

                    @endif

                @endforeach

                {{-- Siguiente --}}
                @if ($paginator->hasMorePages())

                    <li class="page-item">

                        <a class="page-link"

                           href="{{ $paginator->nextPageUrl() }}">

                            <i class="fas fa-chevron-right"></i>

                        </a>

                    </li>

                @else

                    <li class="page-item disabled">

                        <span class="page-link">

                            <i class="fas fa-chevron-right"></i>

                        </span>

                    </li>

                @endif

            </ul>

        </div>

    </div>

</nav>
@endif