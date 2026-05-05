@extends('layouts.app')

@section('content')

<div class="container">

    <section class="hero">
        <div>
            <span class="badge">Ecommerce + gestión inteligente de fábrica</span>

            <h2>Calzado Lorentina con control de producción, stock y ventas</h2>

            <p>
                Sistema Lorentina es una plataforma web diseñada para modernizar
                la operación de fábricas de calzado. Integra tienda virtual,
                inventario, ventas, empleados, producción y trazabilidad en una
                sola solución visual, práctica y organizada.
            </p>

            <div class="hero-actions">
                <a href="{{ route('productos.index') }}" class="btn">
                    Ver catálogo
                </a>

                <a href="{{ route('carrito.ver') }}" class="btn btn-outline">
                    Ir al carrito
                </a>

                <a href="http://localhost:5173" target="_blank" class="btn btn-outline">
                    Ingresar al panel administrativo
                </a>
            </div>
        </div>

        <div class="hero-card">
            <h3>¿Qué soluciona Lorentina?</h3>

            <ul>
                <li>Centraliza productos, stock, ventas y producción.</li>
                <li>Reduce errores del manejo manual en Excel, WhatsApp o papel.</li>
                <li>Permite visualizar el estado de las órdenes de fabricación.</li>
                <li>Facilita el control del inventario por referencias y sucursales.</li>
                <li>Conecta la parte comercial con la operación interna de la fábrica.</li>
            </ul>
        </div>
    </section>

    <section class="system-overview">
        <div class="overview-card">
            <div class="overview-icon">🛒</div>

            <h3>Tienda virtual</h3>

            <p>
                Presenta los modelos de calzado disponibles, permite consultar
                precios, visualizar imágenes y agregar productos al carrito.
            </p>
        </div>

        <div class="overview-card">
            <div class="overview-icon">📦</div>

            <h3>Inventario y stock</h3>

            <p>
                Apoya el control del inventario por referencia, color, tipo,
                talla y sucursal, facilitando una gestión más clara del stock.
            </p>
        </div>

        <div class="overview-card">
            <div class="overview-icon">🏭</div>

            <h3>Producción</h3>

            <p>
                Permite hacer seguimiento a órdenes de fabricación, responsables,
                estados de producción y trazabilidad de cada proceso.
            </p>
        </div>
    </section>

    <section class="admin-preview">
        <div>
            <span class="badge">Panel administrativo real</span>

            <h2>Acceso al sistema interno de Lorentina</h2>

            <p>
                Desde el panel administrativo se gestiona la operación interna:
                empleados, stock, producción, fabricación, ventas y trazabilidad
                de órdenes. Para ingresar, el usuario debe autenticarse en el
                login del sistema.
            </p>

            <a href="http://localhost:5173" target="_blank" class="btn btn-light">
                Abrir login administrativo
            </a>
        </div>

        <div class="admin-modules">
            <div class="admin-module">Login</div>
            <div class="admin-module">Dashboard</div>
            <div class="admin-module">Empleados</div>
            <div class="admin-module">Stock</div>
            <div class="admin-module">Producción</div>
            <div class="admin-module">Ventas</div>
        </div>
    </section>

    <div class="section-head">
        <div>
            <h2>Productos destacados</h2>
            <p>
                Modelos seleccionados de la fábrica de calzado Lorentina.
            </p>
        </div>

        <a href="{{ route('productos.index') }}" class="btn btn-outline">
            Ver todos
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
                        {{ $producto->descripcion ?? 'Producto de calzado elaborado por Lorentina.' }}
                    </p>

                    <div class="meta">
                        <span>{{ $producto->color ?? 'Color disponible' }}</span>
                        <span>{{ $producto->referencia ?? 'Sin referencia' }}</span>
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
                <h3>No hay productos destacados todavía</h3>

                <p>
                    Cuando registres productos activos, aparecerán en esta sección.
                </p>

                <a href="{{ route('productos.index') }}" class="btn">
                    Ir al catálogo
                </a>
            </div>
        @endforelse
    </div>

</div>

@endsection