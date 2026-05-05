@extends('layouts.app')

@section('content')

<div class="container">

    <div class="section-head">
        <div>
            <h2>Detalle del producto</h2>
            <p>Conoce las características del calzado seleccionado.</p>
        </div>

        <a href="{{ route('productos.index') }}" class="btn btn-outline">
            Volver al catálogo
        </a>
    </div>

    <section class="product-detail">
        <div class="product-detail-image">
            @if($producto->imagen)
                <img src="{{ asset('images/' . $producto->imagen) }}" alt="{{ $producto->nombre_modelo }}">
            @else
                <img src="{{ asset('images/default-shoe.jpg') }}" alt="Calzado Lorentina">
            @endif
        </div>

        <div class="product-detail-info">
            <span class="badge">{{ $producto->tipo ?? 'Calzado Lorentina' }}</span>

            <h1>{{ $producto->nombre_modelo }}</h1>

            <p class="detail-description">
                {{ $producto->descripcion ?? 'Producto de calzado elaborado por Lorentina.' }}
            </p>

            <div class="detail-meta">
                <div>
                    <strong>Referencia</strong>
                    <span>{{ $producto->referencia ?? 'N/A' }}</span>
                </div>

                <div>
                    <strong>Color</strong>
                    <span>{{ $producto->color ?? 'N/A' }}</span>
                </div>

                <div>
                    <strong>Tipo</strong>
                    <span>{{ $producto->tipo ?? 'Calzado' }}</span>
                </div>
            </div>

            <div class="detail-price">
                ${{ number_format($producto->precio_detal, 0, ',', '.') }}
            </div>

            <form action="{{ route('carrito.agregar', $producto->id) }}" method="POST">
                @csrf

                <div class="quantity-box">
                    <label>Cantidad</label>
                    <input type="number" name="cantidad" value="1" min="1">
                </div>

                <div class="hero-actions">
                    <button class="btn" type="submit">
                        Agregar al carrito
                    </button>

                    <a href="{{ route('carrito.ver') }}" class="btn btn-outline">
                        Ver carrito
                    </a>
                </div>
            </form>
        </div>
    </section>

</div>

@endsection