@extends('layouts.app')

@section('content')

<div class="container">

    <div class="section-head">
        <div>
            <h2>Detalle del producto</h2>
            <p>Conoce las características del calzado seleccionado.</p>
        </div>

        <a href="{{ route('productos.index') }}" class="btn btn-outline">
            Volver al catálogo
        </a>
    </div>

    <section class="product-detail">
        <div class="product-gallery">
            @php
                $imagenes = $producto->todas_las_imagenes_src;
                $hasMultiple = count($imagenes) > 1;
            @endphp
            <div class="main-image-container">
                <img src="{{ $imagenes[0] }}" alt="{{ $producto->nombre_modelo }}" id="mainDetailImage">
            </div>
            
            @if($hasMultiple)
                <div class="thumbnails-grid">
                    @foreach($imagenes as $idx => $img)
                        <div class="thumb {{ $idx == 0 ? 'active' : '' }}" onclick="changeDetailImage('{{ $img }}', this)">
                            <img src="{{ $img }}" alt="Vista {{ $idx + 1 }}">
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="product-detail-info">
            <span class="badge">{{ $producto->tipo ?? 'Calzado Lorentina' }}</span>

            <h1>{{ $producto->nombre_modelo }}</h1>

            <p class="detail-description">
                {{ $producto->descripcion ?? 'Producto de calzado elaborado por Lorentina.' }}
            </p>

            <div class="detail-meta">
                <div>
                    <strong>Referencia</strong>
                    <span>{{ $producto->referencia ?? 'N/A' }}</span>
                </div>

                <div>
                    <strong>Color</strong>
                    <span>{{ $producto->color ?? 'N/A' }}</span>
                </div>

                <div>
                    <strong>Tipo</strong>
                    <span>{{ $producto->tipo ?? 'Calzado' }}</span>
                </div>
            </div>

            <div class="detail-price">
                ${{ number_format($producto->precio_detal, 0, ',', '.') }}
            </div>

            <form action="{{ route('carrito.agregar', $producto->id) }}" method="POST">
                @csrf

                <div class="stock-info" style="margin: 2rem 0; background: #fffaf5; padding: 20px; border-radius: 18px; border: 1px solid #e2cdb8;">
                    <h3 style="margin-top: 0; color: var(--primary); font-size: 1rem;">Selecciona cantidades por talla:</h3>
                    <p style="font-size: 0.85rem; color: #666; margin-bottom: 1.5rem;">Ingresa el número de pares que deseas para cada talla disponible.</p>
                    
                    <div class="sizes-matrix" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(75px, 1fr)); gap: 12px;">
                        @forelse($producto->tallas_disponibles as $talla)
                            <div class="size-item" style="display: flex; flex-direction: column; align-items: center; gap: 8px; background: white; border: 1.5px solid #dcdcdc; padding: 12px 6px; border-radius: 12px; transition: 0.2s;">
                                <span style="font-size: 0.9rem; font-weight: 800; color: var(--primary);">{{ $talla }}</span>
                                <input type="number" 
                                       name="tallas[{{ $talla }}]" 
                                       value="0" 
                                       min="0" 
                                       style="width: 100%; border: none; background: #fbf9f4; border-radius: 6px; text-align: center; font-family: 'Manrope', sans-serif; font-weight: 700; color: var(--primary); font-size: 1rem; padding: 5px 0;"
                                       onfocus="if(this.value=='0')this.value=''" 
                                       onblur="if(this.value=='')this.value='0'">
                            </div>
                        @empty
                            <div style="grid-column: 1 / -1; text-align: center; padding: 20px; color: #999;">
                                <p>Este producto no tiene stock disponible actualmente.</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                <div class="hero-actions" style="display: flex; gap: 1rem; flex-wrap: wrap;">
                    <button class="btn" type="submit" style="flex: 1; min-width: 200px;">
                        Agregar pedido al carrito
                    </button>

                    <a href="https://wa.me/573000000000?text=Hola!%20Me%20gustaría%20mandar%20a%20fabricar%20la%20referencia%20{{ $producto->referencia }}%20en%20color%20{{ $producto->color }}." 
                       target="_blank"
                       class="btn btn-outline" 
                       style="flex: 1; min-width: 200px; text-align: center;">
                        🔨 Fabricar otras tallas
                    </a>
                </div>
            </form>
        </div>
    </section>

</div>

@endsection

@push('styles')
<style>
    .product-gallery {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }

    .main-image-container {
        width: 100%;
        height: 500px;
        background: #fbf9f4;
        border-radius: 24px;
        overflow: hidden;
        border: 1px solid #e2cdb8;
    }

    .main-image-container img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s ease;
    }

    .thumbnails-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(80px, 1fr));
        gap: 12px;
    }

    .thumb {
        aspect-ratio: 1;
        border-radius: 12px;
        overflow: hidden;
        border: 2px solid transparent;
        cursor: pointer;
        transition: all 0.2s ease;
        background: #fbf9f4;
    }

    .thumb img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .thumb:hover {
        transform: translateY(-3px);
    }

    .thumb.active {
        border-color: var(--chocolate);
        box-shadow: 0 4px 12px rgba(91, 47, 45, 0.15);
    }

    @media (max-width: 900px) {
        .main-image-container {
            height: 400px;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    function changeDetailImage(src, thumb) {
        const mainImg = document.getElementById('mainDetailImage');
        
        // Transition
        mainImg.style.opacity = '0';
        
        setTimeout(() => {
            mainImg.src = src;
            mainImg.style.opacity = '1';
            
            // Update active state
            document.querySelectorAll('.thumb').forEach(t => t.classList.remove('active'));
            thumb.classList.add('active');
        }, 200);
    }
</script>
@endpush
