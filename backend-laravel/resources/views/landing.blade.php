@extends('layouts.app')

@section('content')

<div class="container">
    <section class="hero">
        <div>
            <h2>Gestión inteligente para fábricas de calzado</h2>

            <p>
                Sistema Lorentina es una aplicación web diseñada para modernizar
                y centralizar la operación de fábricas de calzado, integrando
                catálogo, inventario, ventas y control de productos en una sola plataforma.
            </p>

            <a href="{{ route('productos.index') }}" class="btn">
                Ver catálogo
            </a>
        </div>

        <div class="hero-box">
            <h3>¿Qué soluciona Lorentina?</h3>
            <p>
                Reduce el manejo manual en Excel, WhatsApp o papel,
                mejora el control del stock y permite visualizar los productos
                disponibles de forma clara, rápida y organizada.
            </p>

            <p>
                Su propuesta de valor está en optimizar el flujo de producción,
                inventario y venta de calzado mediante una plataforma moderna.
            </p>
        </div>
    </section>

    <h2>Productos destacados</h2>

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
                    <p>{{ $producto->descripcion }}</p>
                    <p class="price">${{ number_format($producto->precio_detal, 0, ',', '.') }}</p>

                    <form action="{{ route('carrito.agregar', $producto->id) }}" method="POST">
                        @csrf
                        <button class="btn" type="submit">
                            Agregar al carrito
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <p>No hay productos registrados todavía.</p>
        @endforelse
    </div>
</div>

@endsection