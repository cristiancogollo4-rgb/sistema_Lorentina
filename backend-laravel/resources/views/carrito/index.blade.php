@extends('layouts.app')

@section('content')

<div class="container">
    <h2>Carrito de compras</h2>

    @if(count($carrito) > 0)
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
                                <img src="{{ asset('images/' . $item['imagen']) }}" width="80" alt="{{ $item['nombre'] }}">
                            @else
                                <img src="{{ asset('images/default-shoe.jpg') }}" width="80" alt="Calzado Lorentina">
                            @endif
                        </td>

                        <td>{{ $item['nombre'] }}</td>

                        <td>
                            ${{ number_format($item['precio'], 0, ',', '.') }}
                        </td>

                        <td>{{ $item['cantidad'] }}</td>

                        <td>
                            ${{ number_format($subtotal, 0, ',', '.') }}
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

        <h2>
            Total: ${{ number_format($total, 0, ',', '.') }}
        </h2>

        <form action="{{ route('carrito.vaciar') }}" method="POST">
            @csrf
            <button class="btn btn-outline" type="submit">
                Vaciar carrito
            </button>
        </form>
    @else
        <p>Tu carrito está vacío.</p>

        <a href="{{ route('productos.index') }}" class="btn">
            Ver productos
        </a>
    @endif
</div>

@endsection