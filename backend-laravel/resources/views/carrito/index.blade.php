@extends('layouts.app')

@section('content')

<div class="container">
    <div class="section-head">
        <div>
            <h2>🛒 Carrito de compras</h2>
            <p>Revisa los productos seleccionados antes de continuar.</p>
        </div>

        <a href="{{ route('productos.index') }}" class="btn btn-outline">
            Seguir comprando
        </a>
    </div>

    @if(count($carrito) > 0)
        <div class="cart-card" data-cart-state='@json($carrito)'>
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

                    @foreach($carrito as $key => $item)
                        @php
                            $subtotal = $item['precio'] * $item['cantidad'];
                            $total += $subtotal;
                            $imagenCarrito = $item['imagen'] ?? null;
                            $imagenCarritoSrc = $imagenCarrito
                                ? (str_starts_with($imagenCarrito, 'http') ? $imagenCarrito : asset('images/' . $imagenCarrito))
                                : asset('images/LOGOLORENTINA.png');
                            $referencia = $item['referencia'] ?? '';
                            $color = $item['color'] ?? '';
                        @endphp

                        <tr>
                            <td>
                                <img class="cart-img" src="{{ $imagenCarritoSrc }}" alt="{{ $item['nombre'] }}" loading="lazy">
                            </td>

                            <td>
                                <strong>{{ $item['nombre'] }}</strong>
                                <br>
                                @if($referencia || $color)
                                    <span class="cart-ref">Ref: {{ $referencia }} {{ $color ? '- ' . $color : '' }}</span>
                                    <br>
                                @endif
                                <span class="cart-size">Talla: {{ $item['talla'] }}</span>
                            </td>

                            <td>
                                ${{ number_format($item['precio'], 0, ',', '.') }}
                            </td>

                            <td>
                                <form action="{{ route('carrito.actualizar', $key) }}" method="POST" class="cart-quantity-form">
                                    @csrf

                                    <input
                                        type="number"
                                        name="cantidad"
                                        value="{{ $item['cantidad'] }}"
                                        min="1"
                                        class="cart-quantity-input"
                                    >

                                    <button class="btn btn-outline" type="submit">
                                        Actualizar
                                    </button>
                                </form>
                            </td>

                            <td>
                                <strong>${{ number_format($subtotal, 0, ',', '.') }}</strong>
                            </td>

                            <td>
                                <form action="{{ route('carrito.eliminar', $key) }}" method="POST">
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

            <div class="order-confirmation">
                <h3>Resumen antes de enviar</h3>
                <div class="order-summary">
                    @foreach($carrito as $item)
                        <div>
                            <span>{{ $item['referencia'] ?? $item['nombre'] }} {{ $item['color'] ?? '' }} - T.{{ $item['talla'] }}</span>
                            <strong>{{ $item['cantidad'] }} {{ $item['cantidad'] == 1 ? 'par' : 'pares' }}</strong>
                        </div>
                    @endforeach
                </div>
                <p>Tu pedido será enviado por WhatsApp para coordinar pago, fabricación o envío.</p>

                <div class="order-actions">
                    <a href="{{ route('checkout.index') }}" class="btn btn-whatsapp">
                        Finalizar compra
                    </a>

                    <form action="{{ route('carrito.vaciar') }}" method="POST">
                        @csrf
                        <button class="btn btn-outline" type="submit">
                            Vaciar carrito
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @else
        <div class="empty-box">
            <h3>🛒 Tu carrito está vacío</h3>

            <p>
                Agrega productos del catálogo para verlos aquí.
            </p>

            <a href="{{ route('productos.index') }}" class="carrito-empty-btn">
                Ver productos
            </a>
        </div>
    @endif
</div>

@endsection
