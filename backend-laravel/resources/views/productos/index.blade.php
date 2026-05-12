@extends('layouts.app')

@section('content')

<div class="container">
    <div class="section-head catalog-head">
        <div>
            <h2>Catálogo de calzado Lorentina</h2>
            <p>Explora los productos disponibles de la fábrica.</p>
        </div>

        <a href="{{ route('carrito.ver') }}" class="btn btn-outline">
            🛒 Ver carrito
        </a>
    </div>

    <div class="filter-bar catalog-filters">
        <div class="filter-group">
            <span class="filter-label">Categorías:</span>
            <div class="filter-options">
                <a href="{{ request()->fullUrlWithQuery(['tipo' => null, 'page' => null]) }}" class="btn {{ !request('tipo') ? 'btn-primary' : 'btn-outline' }}">Todas</a>
                <a href="{{ request()->fullUrlWithQuery(['tipo' => 'romana', 'page' => null]) }}" class="btn {{ request('tipo') == 'romana' ? 'btn-primary' : 'btn-outline' }}">Romanas</a>
                <a href="{{ request()->fullUrlWithQuery(['tipo' => 'clasica', 'page' => null]) }}" class="btn {{ request('tipo') == 'clasica' ? 'btn-primary' : 'btn-outline' }}">Clásicas</a>
                <a href="{{ request()->fullUrlWithQuery(['tipo' => 'plataforma', 'page' => null]) }}" class="btn {{ request('tipo') == 'plataforma' ? 'btn-primary' : 'btn-outline' }}">Plataformas</a>
            </div>
        </div>

        @php
            $hasAdvancedFilters = request()->filled('search') || request()->filled('talla') || request()->filled('color');
        @endphp

        <button
            class="btn btn-outline catalog-filter-toggle"
            type="button"
            data-filter-toggle
            aria-expanded="{{ $hasAdvancedFilters ? 'true' : 'false' }}"
            aria-controls="advancedCatalogFilters"
        >
            {{ $hasAdvancedFilters ? 'Ocultar filtros' : 'Filtrar más' }}
        </button>

        <div
            id="advancedCatalogFilters"
            class="advanced-catalog-filters {{ $hasAdvancedFilters ? 'is-open' : '' }}"
            data-filter-panel
        >
            <form action="{{ route('productos.index') }}" method="GET" class="catalog-search" id="catalogSearchForm">
                @if(request('tipo')) <input type="hidden" name="tipo" value="{{ request('tipo') }}"> @endif
                @if(request('talla')) <input type="hidden" name="talla" value="{{ request('talla') }}"> @endif
                @if(request('color')) <input type="hidden" name="color" value="{{ request('color') }}"> @endif

                <input
                    type="search"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Buscar por modelo, referencia o color..."
                    autocomplete="off"
                    data-live-search
                >

                @if(request('search'))
                    <a href="{{ request()->fullUrlWithQuery(['search' => null, 'page' => null]) }}" class="btn btn-outline">Limpiar</a>
                @endif
            </form>

            <div class="filter-group">
                <span class="filter-label">Talla:</span>
                <div class="filter-options">
                    @foreach([35, 36, 37, 38, 39, 40, 41, 42] as $t)
                        <a href="{{ request()->fullUrlWithQuery(['talla' => $t, 'page' => null]) }}"
                           class="btn {{ request('talla') == $t ? 'btn-primary' : 'btn-outline' }}">
                            {{ $t }}
                        </a>
                    @endforeach
                    @if(request('talla'))
                        <a href="{{ request()->fullUrlWithQuery(['talla' => null, 'page' => null]) }}" class="btn btn-outline filter-clear">
                            Quitar
                        </a>
                    @endif
                </div>
            </div>

            <div class="filter-group">
                <span class="filter-label">Color:</span>
                <div class="filter-options">
                    <a href="{{ request()->fullUrlWithQuery(['color' => null, 'page' => null]) }}"
                       class="btn {{ !request('color') ? 'btn-primary' : 'btn-outline' }}">
                        Todos
                    </a>
                    @foreach($colores as $cRaw)
                        @php
                            $c = is_array($cRaw) ? (string) ($cRaw['color'] ?? reset($cRaw) ?? '') : (string) $cRaw;
                        @endphp
                        @continue($c === '')
                        <a href="{{ request()->fullUrlWithQuery(['color' => $c, 'page' => null]) }}"
                           class="btn {{ request('color') == $c ? 'btn-primary' : 'btn-outline' }}">
                            {{ ucfirst(strtolower($c)) }}
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-products">
        @forelse($productos as $producto)
            @php
                $imagenes = $producto->todas_las_imagenes_src;
                $hasMultiple = count($imagenes) > 1;
            @endphp
            <div class="card product-card"
                 data-images='@json($imagenes)'
                 onmouseenter="startCarousel(this)"
                 onmouseleave="stopCarousel(this)">
                <div class="card-img">
                    <span class="tag">
                        {{ $producto->tipo ?? 'Calzado' }}
                    </span>

                    @if($producto->tiene_stock_bajo)
                        <span class="stock-alert">¡Últimas unidades!</span>
                    @endif

                    <img src="{{ $imagenes[0] }}" alt="{{ $producto->nombre_modelo }}" class="main-img" loading="lazy">

                    @if($hasMultiple)
                        <div class="carousel-dots">
                            @foreach($imagenes as $idx => $img)
                                <span class="dot {{ $idx == 0 ? 'active' : '' }}"></span>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="card-content">
                    <h3>{{ $producto->nombre_modelo }}</h3>

                    <p>
                        {{ $producto->descripcion ?? 'Producto de calzado fabricado por Lorentina.' }}
                    </p>

                    <p class="ref">Ref: {{ $producto->referencia }} - {{ $producto->color }}</p>

                    <div class="stock-info">
                        <span class="stock-label">Tallas disponibles:</span>
                        <div class="sizes-list">
                            @forelse($producto->stock_por_talla as $talla => $cantidad)
                                <span class="size-tag {{ request('talla') == $talla ? 'active' : '' }}">
                                    {{ $talla }}
                                    @if($cantidad < 3)
                                        <small>{{ $cantidad }}</small>
                                    @endif
                                </span>
                            @empty
                                <span class="stock-empty">Sin stock</span>
                            @endforelse
                        </div>
                    </div>

                    <div class="price-row">
                        <span class="price">${{ number_format($producto->precio_detal, 0, ',', '.') }}</span>
                        <a href="{{ route('productos.show', $producto->id) }}" class="btn btn-primary">
                            Ver detalle
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="empty-box">
                <h3>No hay productos disponibles</h3>
                <p>Prueba con otra búsqueda o cambia los filtros activos.</p>
            </div>
        @endforelse
    </div>

    @if ($productos->hasPages())
        <div class="custom-pagination">
            @if ($productos->onFirstPage())
                <span class="page-disabled">Anterior</span>
            @else
                <a href="{{ $productos->previousPageUrl() }}">Anterior</a>
            @endif

            @php
                $currentPage = $productos->currentPage();
                $lastPage = $productos->lastPage();
                $start = max($currentPage - 2, 1);
                $end = min($currentPage + 2, $lastPage);
            @endphp

            @if($start > 1)
                <a href="{{ $productos->url(1) }}">1</a>

                @if($start > 2)
                    <span class="page-dots">...</span>
                @endif
            @endif

            @for($page = $start; $page <= $end; $page++)
                @if ($page == $currentPage)
                    <span class="page-active">{{ $page }}</span>
                @else
                    <a href="{{ $productos->url($page) }}">{{ $page }}</a>
                @endif
            @endfor

            @if($end < $lastPage)
                @if($end < $lastPage - 1)
                    <span class="page-dots">...</span>
                @endif

                <a href="{{ $productos->url($lastPage) }}">{{ $lastPage }}</a>
            @endif

            @if ($productos->hasMorePages())
                <a href="{{ $productos->nextPageUrl() }}">Siguiente</a>
            @else
                <span class="page-disabled">Siguiente</span>
            @endif
        </div>
    @endif
</div>

@endsection
