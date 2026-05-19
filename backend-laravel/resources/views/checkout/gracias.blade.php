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

        <div class="payment-status">
            <span>Metodo de pago</span>
            <strong>{{ $pedido->metodo_pago === 'mercado_pago' ? 'Mercado Pago' : 'WhatsApp' }}</strong>
        </div>

        <div class="payment-status">
            <span>Estado del pago</span>
            <strong>{{ ucfirst(str_replace('_', ' ', $pedido->estado_pago)) }}</strong>
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

        @if ($mercadoPagoData && $mercadoPagoData['init_point'])
            <div class="alert">
                Tu pedido quedo listo. Continua a Mercado Pago para completar el pago en linea.
            </div>

            <a href="{{ $mercadoPagoData['init_point'] }}" target="_blank" rel="noopener noreferrer" class="btn payment-gateway-form">
                Pagar con Mercado Pago
            </a>
        @elseif ($pedido->metodo_pago === 'mercado_pago')
            <div class="alert">
                No se pudo crear el enlace de pago. Revisa el Access Token de Mercado Pago o envia el pedido por WhatsApp.
            </div>
        @else
            <div class="alert">
                Tu pedido quedo listo. Envia el resumen por WhatsApp para coordinar el pago y la entrega.
            </div>
        @endif

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
