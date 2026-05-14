@extends('layouts.app')

@section('content')

<div class="container">
    <div class="section-head">
        <div>
            <h2>Crear cuenta</h2>
            <p>Guarda tus datos para comprar mas rapido y revisar tus pedidos.</p>
        </div>

        <a href="{{ route('cliente.login') }}" class="btn btn-outline">
            Ya tengo cuenta
        </a>
    </div>

    <section class="checkout-panel">
        <form action="{{ route('cliente.registro.guardar') }}" method="POST">
            @csrf

            <div class="form-grid">
                <div class="form-group">
                    <label>Nombre completo</label>
                    <input type="text" name="nombre" value="{{ old('nombre') }}" required>
                    @error('nombre') <small>{{ $message }}</small> @enderror
                </div>

                <div class="form-group">
                    <label>Telefono / WhatsApp</label>
                    <input
                        type="tel"
                        name="telefono"
                        value="{{ old('telefono') }}"
                        inputmode="numeric"
                        pattern="[0-9]{7,15}"
                        maxlength="15"
                        oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                        required
                    >
                    @error('telefono') <small>{{ $message }}</small> @enderror
                </div>

                <div class="form-group">
                    <label>Correo</label>
                    <input type="email" name="email" value="{{ old('email') }}" autocomplete="email" required>
                    @error('email') <small>{{ $message }}</small> @enderror
                </div>

            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label>Contrasena</label>
                    <input type="password" name="password" autocomplete="new-password" required>
                    @error('password') <small>{{ $message }}</small> @enderror
                </div>

                <div class="form-group">
                    <label>Confirmar contrasena</label>
                    <input type="password" name="password_confirmation" autocomplete="new-password" required>
                </div>
            </div>

            <button class="btn" type="submit">Crear cuenta</button>
        </form>
    </section>
</div>

@endsection
