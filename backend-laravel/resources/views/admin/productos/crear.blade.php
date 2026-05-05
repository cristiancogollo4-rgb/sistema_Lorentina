@extends('layouts.app')

@section('content')

<div class="container">
    <div class="section-head">
        <div>
            <h2>Agregar producto</h2>
            <p>Registra un nuevo producto para mostrarlo en el catálogo.</p>
        </div>

        <a href="{{ route('admin.productos.index') }}" class="btn btn-outline">
            Volver
        </a>
    </div>

    @if($errors->any())
        <div class="alert">
            <strong>Revisa los campos:</strong>
            <br>
            @foreach($errors->all() as $error)
                {{ $error }}<br>
            @endforeach
        </div>
    @endif

    <div class="cart-card">
        <form action="{{ route('admin.productos.guardar') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="form-grid">
                <div class="form-group">
                    <label>Nombre del modelo</label>
                    <input type="text" name="nombre_modelo" value="{{ old('nombre_modelo') }}" required>
                </div>

                <div class="form-group">
                    <label>Referencia</label>
                    <input type="text" name="referencia" value="{{ old('referencia') }}">
                </div>

                <div class="form-group">
                    <label>Color</label>
                    <input type="text" name="color" value="{{ old('color') }}">
                </div>

                <div class="form-group">
                    <label>Tipo</label>
                    <input type="text" name="tipo" value="{{ old('tipo') }}" placeholder="Sandalia, zapato, tacón...">
                </div>

                <div class="form-group">
                    <label>Precio detal</label>
                    <input type="number" name="precio_detal" value="{{ old('precio_detal') }}" required>
                </div>

                <div class="form-group">
                    <label>Precio mayor</label>
                    <input type="number" name="precio_mayor" value="{{ old('precio_mayor') }}" required>
                </div>

                <div class="form-group">
                    <label>Costo de producción</label>
                    <input type="number" name="costo_produccion" value="{{ old('costo_produccion') }}" required>
                </div>

                <div class="form-group">
                    <label>Imagen del producto</label>
                    <input type="file" name="imagen" accept="image/*">
                </div>
            </div>

            <div class="form-group">
                <label>Descripción</label>
                <textarea name="descripcion" rows="4">{{ old('descripcion') }}</textarea>
            </div>

            <br>

            <button class="btn" type="submit">
                Guardar producto
            </button>
        </form>
    </div>
</div>

@endsection