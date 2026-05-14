@extends('layouts.app')

@section('content')

<div class="container">
    <div class="section-head">
        <div>
            <h2>Ingresar</h2>
            <p>Entra con tu correo y contrasena para ver tus datos y pedidos.</p>
        </div>

    </div>

    <section class="checkout-panel">
        <div class="account-callout">
            <div>
                <strong>Si no tienes cuenta, puedes crearla</strong>
                <p>Tambien puedes seguir comprando como invitado desde el checkout.</p>
            </div>
            <a href="{{ route('cliente.registro') }}" class="btn btn-outline">Crear cuenta</a>
        </div>

        <form action="{{ route('cliente.login.guardar') }}" method="POST">
            @csrf

            <div class="form-grid">
                <div class="form-group">
                    <label>Correo</label>
                    <input type="email" name="email" value="{{ old('email') }}" autocomplete="email" required>
                    @error('email') <small>{{ $message }}</small> @enderror
                </div>

                <div class="form-group">
                    <label>Contrasena</label>
                    <input type="password" name="password" autocomplete="current-password" required>
                    @error('password') <small>{{ $message }}</small> @enderror
                </div>
            </div>

            <button class="btn" type="submit">Ingresar</button>
        </form>
    </section>
</div>

@endsection
