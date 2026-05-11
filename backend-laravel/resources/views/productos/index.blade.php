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
        <a href="{{ route('productos.index') }}" class="btn {{ !request('tipo') ? '' : 'btn-outline' }}">
            Todas
        </a>
        <a href="{{ route('productos.index', ['tipo' => 'romana']) }}" class="btn {{ request('tipo') == 'romana' ? '' : 'btn-outline' }}">
            Romanas
        </a>
        <a href="{{ route('productos.index', ['tipo' => 'clasica']) }}" class="btn {{ request('tipo') == 'clasica' ? '' : 'btn-outline' }}">
            Clásicas
        </a>
        <a href="{{ route('productos.index', ['tipo' => 'plataforma']) }}" class="btn {{ request('tipo') == 'plataforma' ? '' : 'btn-outline' }}">
            Plataformas
        </a>
    </div>

    <div class="grid">
        @forelse($productos as $producto)
            <div class="card">
                <div class="card-img">
                    <span class="tag">
                        {{ $producto->tipo ?? 'Calzado' }}
                    </span>

                    <img src="{{ $producto->imagen_src }}" alt="{{ $producto->nombre_modelo }}" loading="lazy">
                </div>

                <div class="card-content">
                    <h3>{{ $producto->nombre_modelo }}</h3>

                    <p>
                        {{ $producto->descripcion ?? 'Producto de calzado fabricado por Lorentina.' }}
                    </p>

                    <div class="meta">
                        <span>Ref: {{ $producto->referencia ?? 'N/A' }}</span>
                        <span>{{ $producto->color ?? 'Sin color' }}</span>
                        <span>{{ $producto->tipo ?? 'Calzado' }}</span>
                    </div>

                    <div class="price-row">
                        <p class="price">
                            ${{ number_format($producto->precio_detal, 0, ',', '.') }}
                        </p>
                    </div>

                    <form action="{{ route('carrito.agregar', $producto->id) }}" method="POST">
                        @csrf

                        <div class="quantity-box">
                            <label>Cantidad</label>
                            <input type="number" name="cantidad" value="1" min="1">
                        </div>

                        <button class="btn" type="submit">
                            Agregar al carrito
                        </button>
                    </form>

                    <br>

                    <a href="{{ route('productos.show', $producto->id) }}" class="btn btn-outline">
                        Ver detalle
                    </a>
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
