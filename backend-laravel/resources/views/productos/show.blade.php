@extends('layouts.app')

@section('content')

@php
    $imagenes = $producto->todas_las_imagenes_src;
    $hasMultiple = count($imagenes) > 1;
    $whatsappNumber = config('services.lorentina.whatsapp_number', '573000000000');
@endphp

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
            <button class="main-image-container" type="button" onclick="openLightbox()" aria-label="Ampliar imagen del producto">
                <img src="{{ $imagenes[0] }}" alt="{{ $producto->nombre_modelo }}" id="mainDetailImage">
                <span class="zoom-hint">🔍 Ampliar</span>
            </button>

            @if($hasMultiple)
                <div class="thumbnails-grid">
                    @foreach($imagenes as $idx => $img)
                        <button class="thumb {{ $idx == 0 ? 'active' : '' }}" type="button" onclick="changeDetailImage('{{ $img }}', this)" aria-label="Ver imagen {{ $idx + 1 }}">
                            <img src="{{ $img }}" alt="Vista {{ $idx + 1 }}" loading="lazy">
                        </button>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="product-detail-info">
            <span class="badge">{{ $producto->tipo ?? 'Calzado Lorentina' }}</span>

            <h1>{{ $producto->nombre_modelo }}</h1>

            @if($producto->tiene_stock_bajo)
                <div class="stock-alert detail-stock-alert">
                    ¡Últimas unidades!
                </div>
            @endif

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

                <div class="stock-panel">
                    <h3>Selecciona cantidades por talla:</h3>
                    <p>Ingresa el número de pares que deseas para cada talla disponible.</p>

                    <div class="sizes-matrix">
                        @forelse($producto->stock_por_talla as $talla => $cantidad)
                            <label class="size-item">
                                <span>{{ $talla }}</span>
                                @if($cantidad < 3)
                                    <small>Quedan {{ $cantidad }}</small>
                                @endif
                                <input
                                    type="number"
                                    name="tallas[{{ $talla }}]"
                                    value="0"
                                    min="0"
                                    max="{{ $cantidad }}"
                                    onfocus="if(this.value=='0')this.value=''"
                                    onblur="if(this.value=='')this.value='0'"
                                >
                            </label>
                        @empty
                            <div class="stock-empty-detail">
                                Este producto no tiene stock disponible actualmente.
                            </div>
                        @endforelse
                    </div>
                </div>

                <div class="hero-actions product-actions">
                    <button class="btn" type="submit">
                        Agregar pedido al carrito
                    </button>

                    <a href="https://wa.me/{{ $whatsappNumber }}?text={{ rawurlencode('Hola, me gustaría mandar a fabricar la referencia ' . $producto->referencia . ' en color ' . $producto->color . '.') }}"
                       target="_blank"
                       rel="noopener"
                       class="btn btn-outline">
                        🔨 Fabricar otras tallas
                    </a>

                    <button type="button" class="btn btn-whatsapp" onclick="shareProduct()">
                        Compartir por WhatsApp
                    </button>
                </div>
            </form>
        </div>
    </section>

    <div id="lightbox" class="lightbox" onclick="closeLightbox()" role="dialog" aria-modal="true" aria-label="Imagen ampliada">
        <button class="close-lightbox" type="button" onclick="closeLightbox()" aria-label="Cerrar">&times;</button>
        <img class="lightbox-content" id="lightbox-img" alt="{{ $producto->nombre_modelo }}">
    </div>
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
        position: relative;
        width: 100%;
        height: 500px;
        background: #fbf9f4;
        border-radius: 24px;
        overflow: hidden;
        border: 1px solid #e2cdb8;
        cursor: zoom-in;
        padding: 0;
    }

    .main-image-container img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s ease;
    }

    .main-image-container:hover img {
        transform: scale(1.03);
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
        padding: 0;
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

    .zoom-hint {
        position: absolute;
        right: 12px;
        bottom: 12px;
        background: rgba(50, 34, 20, 0.72);
        color: white;
        padding: 6px 12px;
        border-radius: 999px;
        font-size: 0.78rem;
        font-weight: 800;
        pointer-events: none;
    }

    .stock-panel {
        margin: 2rem 0;
        background: #fffaf5;
        padding: 20px;
        border-radius: 18px;
        border: 1px solid #e2cdb8;
    }

    .stock-panel h3 {
        margin-top: 0;
        color: var(--primary);
        font-size: 1rem;
    }

    .stock-panel p {
        font-size: 0.85rem;
        color: #666;
        margin-bottom: 1.5rem;
    }

    .sizes-matrix {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(82px, 1fr));
        gap: 12px;
    }

    .size-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 7px;
        background: white;
        border: 1.5px solid #dcdcdc;
        padding: 12px 6px;
        border-radius: 12px;
    }

    .size-item span {
        font-size: 0.9rem;
        font-weight: 800;
        color: var(--primary);
    }

    .size-item small {
        color: #b6452c;
        font-size: 0.68rem;
        font-weight: 800;
    }

    .size-item input {
        width: 100%;
        border: none;
        background: #fbf9f4;
        border-radius: 6px;
        text-align: center;
        font-family: 'Manrope', sans-serif;
        font-weight: 700;
        color: var(--primary);
        font-size: 1rem;
        padding: 5px 0;
    }

    .product-actions .btn {
        flex: 1;
        min-width: 190px;
        text-align: center;
    }

    .btn-whatsapp {
        background: #25d366;
        border-color: #25d366;
    }

    .lightbox {
        align-items: center;
        background-color: rgba(0, 0, 0, 0.92);
        backdrop-filter: blur(5px);
        display: none;
        inset: 0;
        justify-content: center;
        padding: 24px;
        position: fixed;
        z-index: 10000;
    }

    .lightbox.open {
        display: flex;
    }

    .lightbox-content {
        max-width: 94vw;
        max-height: 88vh;
        object-fit: contain;
        border-radius: 12px;
    }

    .close-lightbox {
        position: absolute;
        top: 18px;
        right: 24px;
        color: #f1f1f1;
        font-size: 42px;
        font-weight: bold;
        cursor: pointer;
        background: transparent;
        border: 0;
        line-height: 1;
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

        mainImg.style.opacity = '0';

        setTimeout(() => {
            setImgSrc(mainImg, src);
            mainImg.style.opacity = '1';

            document.querySelectorAll('.thumb').forEach(t => t.classList.remove('active'));
            thumb.classList.add('active');
        }, 180);
    }

    function openLightbox() {
        const modal = document.getElementById('lightbox');
        const img = document.getElementById('mainDetailImage');
        const modalImg = document.getElementById('lightbox-img');

        modal.classList.add('open');
        modalImg.src = img.src;
        document.body.style.overflow = 'hidden';
    }

    function closeLightbox() {
        document.getElementById('lightbox').classList.remove('open');
        document.body.style.overflow = '';
    }

    function shareProduct() {
        const text = [
            'Hola, quiero compartir este producto Lorentina:',
            '',
            '*{{ $producto->nombre_modelo }}*',
            'Ref: {{ $producto->referencia }}',
            'Color: {{ $producto->color }}',
            'Precio: ${{ number_format($producto->precio_detal, 0, ',', '.') }}',
            'Imagen: {{ $imagenes[0] }}',
            '',
            window.location.href
        ].join('\n');

        window.open(`https://wa.me/{{ $whatsappNumber }}?text=${encodeURIComponent(text)}`, '_blank', 'noopener');
    }

    document.addEventListener('keydown', event => {
        if (event.key === 'Escape') {
            closeLightbox();
        }
    });
</script>
@endpush

