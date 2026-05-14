@php
    $selectedDepartamento = old('departamento', $direccion?->departamento);
    $selectedMunicipio = old('municipio', $direccion?->municipio);
@endphp

<form action="{{ $action }}" method="POST" class="address-form">
    @csrf

    <div class="form-group">
        <label>Nombre de la direccion</label>
        <input type="text" name="alias" value="{{ old('alias', $direccion?->alias ?? 'Casa') }}" required>
        @error('alias') <small>{{ $message }}</small> @enderror
    </div>

    <div class="form-group">
        <label>Departamento</label>
        <select name="departamento" class="department-select" data-selected-municipio="{{ $selectedMunicipio }}" required>
            <option value="">Selecciona un departamento</option>
            @foreach ($departamentos as $departamento => $municipios)
                <option value="{{ $departamento }}" @selected($selectedDepartamento === $departamento)>
                    {{ $departamento }}
                </option>
            @endforeach
        </select>
        @error('departamento') <small>{{ $message }}</small> @enderror
    </div>

    <div class="form-group">
        <label>Municipio</label>
        <select name="municipio" class="municipality-select" required>
            <option value="">Selecciona un municipio</option>
        </select>
        @error('municipio') <small>{{ $message }}</small> @enderror
    </div>

    <div class="form-group">
        <label>Direccion</label>
        <input type="text" name="direccion" value="{{ old('direccion', $direccion?->direccion) }}" required>
        @error('direccion') <small>{{ $message }}</small> @enderror
    </div>

    <div class="form-group">
        <label>Detalle adicional</label>
        <input type="text" name="detalle" value="{{ old('detalle', $direccion?->detalle) }}" placeholder="Apartamento, barrio, referencia...">
        @error('detalle') <small>{{ $message }}</small> @enderror
    </div>

    <label class="account-create-toggle">
        <input type="checkbox" name="principal" value="1" @checked(old('principal', $direccion?->principal))>
        Usar como direccion principal
    </label>

    <button class="btn" type="submit">{{ $submit }}</button>
</form>

<script>
    window.lorentinaDepartamentos = @json($departamentos);
</script>
