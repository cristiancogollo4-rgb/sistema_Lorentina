@extends('layouts.app')

@section('content')

<div class="container">
    <div class="section-head">
        <div>
            <h2>Administrar productos</h2>
            <p>Gestiona los productos que aparecerán en el catálogo ecommerce.</p>
        </div>

        <a href="{{ route('admin.productos.crear') }}" class="btn">
            Agregar producto
        </a>
    </div>

    @if($productos->count() > 0)
        <div class="cart-card">
            <table>
                <thead>
                    <tr>
                        <th>Imagen</th>
                        <th>Producto</th>
                        <th>Referencia</th>
                        <th>Color</th>
                        <th>Tipo</th>
                        <th>Precio</th>
                        <th>Estado</th>
                        <th>Acción</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($productos as $producto)
                        <tr>
                            <td>
                                @if($producto->imagen)
                                    <img class="cart-img" src="{{ asset('images/' . $producto->imagen) }}" alt="{{ $producto->nombre_modelo }}">
                                @else
                                    <img class="cart-img" src="{{ asset('images/LOGOLORENTINA.png') }}" alt="Calzado Lorentina">
                                @endif
                            </td>

                            <td>
                                <strong>{{ $producto->nombre_modelo }}</strong>
                            </td>

                            <td>{{ $producto->referencia ?? 'N/A' }}</td>

                            <td>{{ $producto->color ?? 'N/A' }}</td>

                            <td>{{ $producto->tipo ?? 'N/A' }}</td>

                            <td>
                                ${{ number_format($producto->precio_detal, 0, ',', '.') }}
                            </td>

                            <td>
                                @if($producto->activo)
                                    Activo
                                @else
                                    Inactivo
                                @endif
                            </td>

                            <td>
                                <form action="{{ route('admin.productos.estado', $producto->id) }}" method="POST">
                                    @csrf
                                    <button class="btn btn-outline" type="submit">
                                        Cambiar estado
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="pagination">
                {{ $productos->links() }}
            </div>
        </div>
    @else
        <div class="empty-box">
            <h3>No hay productos registrados</h3>
            <p>Agrega tu primer producto para que aparezca en el catálogo.</p>

            <a href="{{ route('admin.productos.crear') }}" class="btn">
                Crear producto
            </a>
        </div>
    @endif
</div>

@endsection
