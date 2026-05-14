@extends('layouts.app')

@section('content')

<div class="container">
    <div class="section-head">
        <div>
            <h2>Finalizar compra</h2>
            <p>Completa tus datos para crear el pedido y coordinar el pago por WhatsApp.</p>
        </div>

        <a href="{{ route('carrito.ver') }}" class="btn btn-outline">
            Volver al carrito
        </a>
    </div>

    <div class="checkout-layout">
        <section class="checkout-panel">
            <h3>Datos de entrega</h3>

            @if ($clienteEcommerce)
                <div class="account-callout">
                    <div>
                        <strong>Comprando como {{ $clienteEcommerce->nombre }}</strong>
                        <p>Puedes usar una direccion guardada o escribir otra para este pedido.</p>
                    </div>
                    <a href="{{ route('cliente.cuenta') }}" class="btn btn-outline">Mi cuenta</a>
                </div>
            @else
                <div class="account-callout">
                    <div>
                        <strong>Tambien puedes comprar como invitado</strong>
                        <p>Inicia sesion solo si quieres guardar tus datos e historial.</p>
                    </div>
                    <div class="account-links">
                        <a href="{{ route('cliente.login') }}" class="btn btn-outline">Ingresar</a>
                        <a href="{{ route('cliente.registro') }}" class="btn btn-outline">Crear cuenta</a>
                    </div>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert">
                    Revisa los campos del formulario antes de continuar.
                </div>
            @endif

            <form action="{{ route('checkout.guardar') }}" method="POST">
                @csrf

                <div class="form-grid">
                    <div class="form-group">
                        <label>Nombre completo</label>
                        <input type="text" name="cliente_nombre" value="{{ old('cliente_nombre', $clienteEcommerce?->nombre) }}" required>
                        @error('cliente_nombre') <small>{{ $message }}</small> @enderror
                    </div>

                    <div class="form-group">
                        <label>Telefono / WhatsApp</label>
                        <input
                            type="tel"
                            name="cliente_telefono"
                            value="{{ old('cliente_telefono', $clienteEcommerce?->telefono) }}"
                            inputmode="numeric"
                            pattern="[0-9]{7,15}"
                            maxlength="15"
                            oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                            required
                        >
                        @error('cliente_telefono') <small>{{ $message }}</small> @enderror
                    </div>

                    <div class="form-group">
                        <label>Correo</label>
                        <input type="email" name="cliente_email" value="{{ old('cliente_email', $clienteEcommerce?->email) }}">
                        @error('cliente_email') <small>{{ $message }}</small> @enderror
                    </div>

                </div>

                @if ($clienteEcommerce && $direcciones->isNotEmpty())
                    <div class="form-group">
                        <label>Direccion guardada</label>
                        <select name="direccion_id">
                            <option value="">Usar otra direccion</option>
                            @foreach ($direcciones as $direccion)
                                <option value="{{ $direccion->id }}" @selected((string) old('direccion_id') === (string) $direccion->id || (!old('direccion_id') && $direccion->principal))>
                                    {{ $direccion->alias }} - {{ $direccion->municipio }}, {{ $direccion->departamento }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div class="form-grid">
                    <div class="form-group">
                        <label>Departamento</label>
                        <select name="cliente_departamento" class="department-select" data-selected-municipio="{{ old('cliente_municipio') }}">
                            <option value="">Selecciona un departamento</option>
                            @foreach ($departamentos as $departamento => $municipios)
                                <option value="{{ $departamento }}" @selected(old('cliente_departamento') === $departamento)>
                                    {{ $departamento }}
                                </option>
                            @endforeach
                        </select>
                        @error('cliente_departamento') <small>{{ $message }}</small> @enderror
                    </div>

                    <div class="form-group">
                        <label>Municipio</label>
                        <select name="cliente_municipio" class="municipality-select">
                            <option value="">Selecciona un municipio</option>
                        </select>
                        @error('cliente_municipio') <small>{{ $message }}</small> @enderror
                    </div>
                </div>

                <div class="form-group">
                    <label>Direccion de entrega</label>
                    <input type="text" name="cliente_direccion" value="{{ old('cliente_direccion') }}">
                    @error('cliente_direccion') <small>{{ $message }}</small> @enderror
                </div>

                @unless ($clienteEcommerce)
                    <div class="account-create-box">
                        <label class="account-create-toggle">
                            <input
                                type="checkbox"
                                name="crear_cuenta"
                                value="1"
                                @checked(old('crear_cuenta'))
                            >
                            Guardar mis datos creando una cuenta con este correo
                        </label>

                        <div class="account-fields">
                            <div class="form-grid">
                                <div class="form-group">
                                    <label>Contrasena</label>
                                    <input type="password" name="password" autocomplete="new-password">
                                    @error('password') <small>{{ $message }}</small> @enderror
                                </div>

                                <div class="form-group">
                                    <label>Confirmar contrasena</label>
                                    <input type="password" name="password_confirmation" autocomplete="new-password">
                                </div>
                            </div>
                        </div>
                    </div>
                @endunless

                <input type="hidden" name="metodo_pago" value="whatsapp">

                <div class="alert">
                    El pago se coordina por WhatsApp despues de crear el pedido.
                </div>

                <div class="form-group">
                    <label>Notas del pedido</label>
                    <textarea name="notas" rows="4" placeholder="Indicaciones de entrega, barrio, horario...">{{ old('notas') }}</textarea>
                    @error('notas') <small>{{ $message }}</small> @enderror
                </div>

                <button class="btn" type="submit">
                    Crear pedido
                </button>
            </form>
        </section>

        <aside class="checkout-panel">
            <h3>Resumen</h3>

            @foreach($carrito as $item)
                <div class="checkout-summary-item">
                    <div>
                        <strong>{{ $item['nombre'] }}</strong>
                        <br>
                        <span>Talla {{ $item['talla'] }} · Cantidad {{ $item['cantidad'] }}</span>
                    </div>
                    <strong>${{ number_format($item['precio'] * $item['cantidad'], 0, ',', '.') }}</strong>
                </div>
            @endforeach

            <div class="checkout-total">
                <span>Total</span>
                <span>${{ number_format($total, 0, ',', '.') }}</span>
            </div>
        </aside>
    </div>
</div>

<script>
    window.lorentinaDepartamentos = @json($departamentos);
</script>

@endsection
