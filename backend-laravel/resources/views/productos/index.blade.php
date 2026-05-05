@extends('layouts.app')

@section('content')

<div class="container">
    <h2>Catálogo de calzado Lorentina</h2>

    <p>
        Explora los productos disponibles de la fábrica de calzado.
    </p>

    <div class="grid">
        @forelse($productos as $producto)
            <div class="card">
                @if($producto->imagen)
                    <img src="{{ asset('images/' . $producto->imagen) }}" alt="{{ $producto->nombre_modelo }}">
                @else
                    <img src="{{ asset('images/default-shoe.jpg') }}" alt="Calzado Lorentina">
                @endif

                <div class="card-content">
                    <h3>{{ $producto->nombre_modelo }}</h3>

                    <p>
                        {{ $producto->descripcion ?? 'Producto de calzado fabricado por Lorentina.' }}
                    </p>

                    <p>
                        <strong>Referencia:</strong> {{ $producto->referencia }}
                    </p>

                    <p>
                        <strong>Color:</strong> {{ $producto->color }}
                    </p>

                    <p>
                        <strong>Tipo:</strong> {{ $producto->tipo }}
                    </p>

                    <p class="price">
                        ${{ number_format($producto->precio_detal, 0, ',', '.') }}
                    </p>

                    <form action="{{ route('carrito.agregar', $producto->id) }}" method="POST">
                        @csrf
                        <button class="btn" type="submit">
                            Agregar al carrito
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <p>No hay productos disponibles.</p>
        @endforelse
    </div>

    <div class="pagination">
        {{ $productos->links() }}
    </div>
</div>

@endsection