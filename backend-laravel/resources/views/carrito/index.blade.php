@extends('layouts.app')

@section('content')

<div class="container">
    <div class="section-head">
        <div>
            <h2>Carrito de compras</h2>
            <p>Revisa los productos seleccionados antes de continuar.</p>
        </div>

        <a href="{{ route('productos.index') }}" class="btn btn-outline">Seguir comprando</a>
    </div>

    @if(count($carrito) > 0)
        <div class="cart-card">
            <table>
                <thead>
                    <tr>
                        <th>Imagen</th>
                        <th>Producto</th>
                        <th>Precio</th>
                        <th>Cantidad</th>
                        <th>Subtotal</th>
                        <th>Acción</th>
                    </tr>
                </thead>

                <tbody>
                    @php
                        $total = 0;
                    @endphp

                    @foreach($carrito as $item)
                        @php
                            $subtotal = $item['precio'] * $item['cantidad'];
                            $total += $subtotal;
                        @endphp

                        <tr>
                            <td>
                                @if($item['imagen'])
                                    <img class="cart-img" src="{{ asset('images/' . $item['imagen']) }}" alt="{{ $item['nombre'] }}">
                                @else
                                    <img class="cart-img" src="{{ asset('images/default-shoe.jpg') }}" alt="Calzado Lorentina">
                                @endif
                            </td>

                            <td>
                                <strong>{{ $item['nombre'] }}</strong>
                            </td>

                            <td>
                                ${{ number_format($item['precio'], 0, ',', '.') }}
                            </td>

                            <td>
                                {{ $item['cantidad'] }}
                            </td>

                            <td>
                                <strong>${{ number_format($subtotal, 0, ',', '.') }}</strong>
                            </td>

                            <td>
                                <form action="{{ route('carrito.eliminar', $item['id']) }}" method="POST">
                                    @csrf
                                    <button class="btn btn-outline" type="submit">
                                        Eliminar
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="cart-total">
                <h2>Total del pedido</h2>
                <h2>${{ number_format($total, 0, ',', '.') }}</h2>
            </div>

            <br>

            <form action="{{ route('carrito.vaciar') }}" method="POST">
                @csrf
                <button class="btn btn-outline" type="submit">
                    Vaciar carrito
                </button>
            </form>
        </div>
    @else
        <div class="empty-box">
            <h3>Tu carrito está vacío</h3>
            <p>Agrega productos del catálogo para verlos aquí.</p>

            <a href="{{ route('productos.index') }}" class="btn">
                Ver productos
            </a>
        </div>
    @endif
</div>

@endsection