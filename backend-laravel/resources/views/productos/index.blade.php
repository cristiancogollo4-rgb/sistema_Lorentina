@extends('layouts.app')

@section('content')

<div class="container">
    <div class="section-head">
        <div>
            <h2>Catálogo de calzado Lorentina</h2>
            <p>Explora los productos disponibles de la fábrica.</p>
        </div>

        <a href="{{ route('carrito.ver') }}" class="btn btn-outline">
            🛒 Ver carrito
        </a>
    </div>

    <div class="filter-bar">
        <div class="filter-group">
            <span class="filter-label">Categorías:</span>
            <div class="filter-options">
                <a href="{{ route('productos.index') }}" class="btn {{ !request('tipo') ? 'btn-primary' : 'btn-outline' }}">Todas</a>
                <a href="{{ route('productos.index', ['tipo' => 'romana']) }}" class="btn {{ request('tipo') == 'romana' ? 'btn-primary' : 'btn-outline' }}">Romanas</a>
                <a href="{{ route('productos.index', ['tipo' => 'clasica']) }}" class="btn {{ request('tipo') == 'clasica' ? 'btn-primary' : 'btn-outline' }}">Clásicas</a>
                <a href="{{ route('productos.index', ['tipo' => 'plataforma']) }}" class="btn {{ request('tipo') == 'plataforma' ? 'btn-primary' : 'btn-outline' }}">Plataformas</a>
            </div>
        </div>

        <div class="filter-group" style="margin-top: 15px;">
            <span class="filter-label">Filtrar por talla:</span>
            <div class="filter-options">
                @foreach([35, 36, 37, 38, 39, 40, 41, 42] as $t)
                    <a href="{{ request()->fullUrlWithQuery(['talla' => $t]) }}" 
                       class="btn {{ request('talla') == $t ? 'btn-primary' : 'btn-outline' }}" 
                       style="padding: 8px 16px; font-size: 0.85rem;">
                        {{ $t }}
                    </a>
                @endforeach
                @if(request('talla'))
                    <a href="{{ request()->fullUrlWithQuery(['talla' => null]) }}" class="btn btn-outline" style="padding: 8px 16px; font-size: 0.85rem; border-color: #ff4d4d; color: #ff4d4d !important;">
                        ✕ Quitar
                    </a>
                @endif
            </div>
        </div>
    </div>

    <div class="grid">
        @forelse($productos as $producto)
            @php
                $imagenes = $producto->todas_las_imagenes_src;
                $hasMultiple = count($imagenes) > 1;
            @endphp
            <div class="card product-card" 
                 data-images="{{ json_encode($imagenes) }}"
                 onmouseenter="startCarousel(this)" 
                 onmouseleave="stopCarousel(this)">
                <div class="card-img">
                    <span class="tag">
                        {{ $producto->tipo ?? 'Calzado' }}
                    </span>

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

                    <p class="ref" style="margin-bottom: 0.5rem;">Ref: {{ $producto->referencia }} - {{ $producto->color }}</p>
                    
                    <div class="stock-info" style="margin-bottom: 1rem;">
                        <span style="font-size: 0.75rem; color: #666; display: block; margin-bottom: 0.4rem; font-weight: 700;">Tallas disponibles:</span>
                        <div class="sizes-list" style="display: flex; gap: 4px; flex-wrap: wrap;">
                            @forelse($producto->tallas_disponibles as $talla)
                                <span class="size-tag {{ request('talla') == $talla ? 'active' : '' }}" 
                                      style="background: {{ request('talla') == $talla ? 'var(--primary)' : '#f0f0f0' }}; 
                                             color: {{ request('talla') == $talla ? 'white' : '#333' }}; 
                                             padding: 2px 6px; border-radius: 4px; font-size: 0.75rem; font-weight: 800;">
                                    {{ $talla }}
                                </span>
                            @empty
                                <span style="font-size: 0.7rem; color: #999;">Sin stock</span>
                            @endforelse
                        </div>
                    </div>

                    <div class="price-row" style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #eee; pt: 1rem;">
                        <span class="price" style="font-size: 1.2rem;">${{ number_format($producto->precio_detal, 0, ',', '.') }}</span>
                        <a href="{{ route('productos.show', $producto->id) }}" class="btn btn-primary" style="padding: 8px 16px; font-size: 0.85rem;">
                            Ver detalle
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="empty-box">
                <h3>No hay productos disponibles</h3>
                <p>Agrega productos desde el módulo administrativo para visualizar el catálogo.</p>
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
