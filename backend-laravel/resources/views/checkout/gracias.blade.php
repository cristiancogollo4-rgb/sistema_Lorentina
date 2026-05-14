@extends('layouts.app')

@section('content')

<div class="container">
    <div class="checkout-panel">
        <span class="badge">Pedido creado</span>
        <h2>Gracias por tu compra</h2>
        <p>
            Tu pedido <strong>{{ $pedido->codigo }}</strong> quedo registrado y esta pendiente de pago.
        </p>

        <div class="checkout-total">
            <span>Total</span>
            <span>${{ number_format($pedido->total, 0, ',', '.') }}</span>
        </div>

        <br>

        <h3>Productos</h3>

        @foreach($pedido->items as $item)
            <div class="checkout-summary-item">
                <div>
                    <strong>{{ $item->nombre }}</strong>
                    <br>
                    <span>Talla {{ $item->talla }} · Cantidad {{ $item->cantidad }}</span>
                </div>
                <strong>${{ number_format($item->subtotal, 0, ',', '.') }}</strong>
            </div>
        @endforeach

        <br>

        <div class="alert">
            Tu pedido quedo listo. Envia el resumen por WhatsApp para coordinar el pago y la entrega.
        </div>

        <div class="hero-actions">
            <a href="{{ $whatsappUrl }}" target="_blank" rel="noopener noreferrer" class="btn">
                Enviar pedido por WhatsApp
            </a>
            <a href="{{ route('productos.index') }}" class="btn">
                Volver al catalogo
            </a>
            <a href="{{ route('landing') }}" class="btn btn-outline">
                Ir al inicio
            </a>
        </div>
    </div>
</div>

@endsection
