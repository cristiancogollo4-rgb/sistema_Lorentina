@extends('layouts.app')

@section('content')

<div class="container">
    <div class="section-head">
        <div>
            <h2>Catálogo de calzado Lorentina</h2>
            <p>Explora los productos disponibles de la fábrica.</p>
        </div>

        <a href="{{ route('carrito.ver') }}" class="btn btn-outline">
            Ver carrito
        </a>
    </div>

    <div class="grid">
        @forelse($productos as $producto)
            <div class="card">
                <div class="card-img">
                    <span class="tag">
                        {{ $producto->tipo ?? 'Calzado' }}
                    </span>

                    @if($producto->imagen)
                        <img src="{{ asset('images/' . $producto->imagen) }}" alt="{{ $producto->nombre_modelo }}">
                    @else
                        <img src="{{ asset('images/default-shoe.jpg') }}" alt="Calzado Lorentina">
                    @endif
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

    <div class="pagination">
        {{ $productos->links() }}
    </div>
</div>

@endsection