@extends('layouts.app')

@section('content')

@php
    $seccionActiva = request('seccion', 'perfil');
@endphp

<div class="container">
    <div class="section-head">
        <div>
            <h2>Mi cuenta</h2>
            <p>Administra tu perfil, tus direcciones y tus pedidos del ecommerce.</p>
        </div>

        <a href="{{ route('productos.index') }}" class="btn btn-outline">
            Ver catalogo
        </a>
    </div>

    <div class="account-tabs">
        <a class="{{ $seccionActiva === 'perfil' ? 'active' : '' }}" href="{{ route('cliente.cuenta', ['seccion' => 'perfil']) }}">Perfil</a>
        <a class="{{ $seccionActiva === 'direcciones' ? 'active' : '' }}" href="{{ route('cliente.cuenta', ['seccion' => 'direcciones']) }}">Direcciones</a>
        <a class="{{ $seccionActiva === 'pedidos' ? 'active' : '' }}" href="{{ route('cliente.cuenta', ['seccion' => 'pedidos']) }}">Mis pedidos</a>
    </div>

    @if ($seccionActiva === 'perfil')
        <section class="checkout-panel">
            <h3>Perfil</h3>

            <div class="detail-meta">
                <div>
                    <small>Nombre</small>
                    <strong>{{ $cliente->nombre }}</strong>
                </div>
                <div>
                    <small>Telefono / WhatsApp</small>
                    <strong>{{ $cliente->telefono }}</strong>
                </div>
                <div>
                    <small>Correo</small>
                    <strong>{{ $cliente->email ?: 'Sin correo' }}</strong>
                </div>
            </div>
        </section>
    @elseif ($seccionActiva === 'direcciones')
        <div class="checkout-layout">
            <section class="checkout-panel">
                <h3>Direcciones guardadas</h3>

                @if ($direcciones->isEmpty())
                    <p>No tienes direcciones guardadas todavia.</p>
                @else
                    <div class="account-orders">
                        @foreach ($direcciones as $direccion)
                            <article class="account-order">
                                <div class="account-order-head">
                                    <div>
                                        <strong>{{ $direccion->alias }}</strong>
                                        @if ($direccion->principal)
                                            <span class="badge">Principal</span>
                                        @endif
                                        <br>
                                        <small>{{ $direccion->municipio }}, {{ $direccion->departamento }} · Colombia</small>
                                    </div>
                                </div>

                                <p>{{ $direccion->direccion }}</p>
                                @if ($direccion->detalle)
                                    <small>{{ $direccion->detalle }}</small>
                                @endif

                                <details class="address-edit">
                                    <summary>Editar direccion</summary>
                                    @include('clientes.partials.direccion-form', [
                                        'action' => route('cliente.direcciones.actualizar', $direccion),
                                        'direccion' => $direccion,
                                        'departamentos' => $departamentos,
                                        'submit' => 'Guardar cambios',
                                    ])
                                </details>
                            </article>
                        @endforeach
                    </div>
                @endif
            </section>

            <aside class="checkout-panel">
                <h3>Anadir direccion</h3>

                @include('clientes.partials.direccion-form', [
                    'action' => route('cliente.direcciones.guardar'),
                    'direccion' => null,
                    'departamentos' => $departamentos,
                    'submit' => 'Guardar direccion',
                ])
            </aside>
        </div>
    @else
        <section class="checkout-panel">
            <h3>Mis pedidos</h3>

            @if ($pedidos->isEmpty())
                <p>Aun no tienes pedidos asociados a esta cuenta.</p>
            @else
                <div class="account-orders">
                    @foreach ($pedidos as $pedido)
                        <article class="account-order">
                            <div class="account-order-head">
                                <div>
                                    <strong>Pedido {{ $pedido->codigo }}</strong>
                                    <br>
                                    <small>Fecha: {{ $pedido->created_at?->format('d/m/Y h:i a') }}</small>
                                </div>
                                <strong>${{ number_format($pedido->total, 0, ',', '.') }}</strong>
                            </div>

                            <small>
                                Estado: {{ ucfirst(str_replace('_', ' ', $pedido->estado)) }}
                                · Pago: WhatsApp
                            </small>

                            <div class="account-order-items">
                                @foreach ($pedido->items as $item)
                                    <span>{{ $item->nombre }} · Talla {{ $item->talla }} · Cant. {{ $item->cantidad }}</span>
                                @endforeach
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </section>
    @endif
</div>

@endsection
