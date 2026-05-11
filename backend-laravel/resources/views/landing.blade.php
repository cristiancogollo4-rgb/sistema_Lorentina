@extends('layouts.app')

@section('content')

<div class="ecommerce-home">
    
    <!-- Hero Section -->
    <section class="hero-premium">
        <div class="hero-content">
            <span class="hero-badge">Colección Artesanal 2026</span>
            <h1>Elegancia y Confort en Cada Paso</h1>
            <p>Descubre nuestra exclusivas sandalias y calzado artesanal, fabricados con el alma y la calidad de Bucaramanga.</p>
            <div class="hero-buttons">
                <a href="{{ route('productos.index') }}" class="btn-primary-gold">Explorar Catálogo</a>
                <a href="#novedades" class="btn-secondary-white">Ver Novedades</a>
            </div>
        </div>
        <div class="hero-image-container">
            <img src="{{ asset('images/hero-lorentina.jpg') }}" alt="Lorentina Calzado Artesanal" class="hero-img">
        </div>
    </section>

    <!-- Categories Section -->
    <section class="categories-grid">
        <div class="category-card">
            <div class="category-icon">👡</div>
            <h3>Sandalias</h3>
            <p>Estilo y frescura</p>
        </div>
        <div class="category-card">
            <div class="category-icon">👠</div>
            <h3>Clásicos</h3>
            <p>Elegancia atemporal</p>
        </div>
        <div class="category-card">
            <div class="category-icon">✨</div>
            <h3>Zaras</h3>
            <p>Tendencia moderna</p>
        </div>
        <div class="category-card">
            <div class="category-icon">🛠️</div>
            <h3>Personalizados</h3>
            <p>A tu medida</p>
        </div>
    </section>

    <!-- Benefits Section -->
    <section class="benefits-strip">
        <div class="benefit-item">
            <div class="benefit-icon">🇨🇴</div>
            <div>
                <h4>Orgullo Local</h4>
                <p>100% Hecho en Bucaramanga</p>
            </div>
        </div>
        <div class="benefit-item">
            <div class="benefit-icon">🚚</div>
            <div>
                <h4>Envío Seguro</h4>
                <p>A todo el país</p>
            </div>
        </div>
        <div class="benefit-item">
            <div class="benefit-icon">🧶</div>
            <div>
                <h4>Materiales Premium</h4>
                <p>Cueros y sintéticos de alta gama</p>
            </div>
        </div>
    </section>

    <!-- Featured Products -->
    <div class="section-head" id="novedades">
        <div>
            <h2>Modelos Destacados</h2>
            <p>Selección especial de nuestra fábrica para ti.</p>
        </div>
        <a href="{{ route('productos.index') }}" class="btn-link">Ver todo el catálogo →</a>
    </div>

    <div class="grid-products">
        @forelse($productos as $producto)
            <div class="product-card">
                <div class="product-img">
                    <span class="product-tag">{{ $producto->tipo ?? 'Nuevo' }}</span>
                    <img src="{{ $producto->imagen_src }}" alt="{{ $producto->nombre_modelo }}">
                </div>
                <div class="product-info">
                    <span class="product-ref">Ref: {{ $producto->referencia ?? 'LR' }}</span>
                    <h3>{{ $producto->nombre_modelo }}</h3>
                    <p class="product-price">${{ number_format($producto->precio_detal, 0, ',', '.') }}</p>
                    <div class="product-actions">
                        <form action="{{ route('carrito.agregar', $producto->id) }}" method="POST" style="flex-grow: 1;">
                            @csrf
                            <input type="hidden" name="cantidad" value="1">
                            <button class="btn-add-cart" type="submit">Agregar</button>
                        </form>
                        <a href="{{ route('productos.show', $producto->id) }}" class="btn-detail">Ver</a>
                    </div>
                </div>
            </div>
        @empty
            <div class="empty-state">
                <p>Próximamente nuevos modelos disponibles.</p>
            </div>
        @endforelse
    </div>

</div>

<style>
    /* Estilos mejorados para el Ecommerce */
    .ecommerce-home {
        padding-top: 20px;
    }

    .hero-premium {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 40px;
        align-items: center;
        background: var(--primary-container);
        border-radius: var(--radio-lg);
        padding: 60px;
        color: white;
        margin-bottom: 40px;
        overflow: hidden;
        box-shadow: var(--sombra-ambient);
    }

    .hero-content {
        z-index: 2;
    }

    .hero-badge {
        background: var(--secondary);
        color: white;
        padding: 6px 16px;
        border-radius: var(--radio-sm);
        font-weight: 800;
        font-size: 12px;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        margin-bottom: 24px;
        display: inline-block;
    }

    .hero-premium h1 {
        font-family: 'Noto Serif', serif;
        font-size: 52px;
        line-height: 1.1;
        margin-bottom: 24px;
        color: var(--blanco);
        font-weight: 700;
    }

    .hero-premium p {
        font-family: 'Manrope', sans-serif;
        font-size: 18px;
        color: var(--surface);
        margin-bottom: 32px;
        max-width: 500px;
        opacity: 0.9;
    }

    .hero-buttons {
        display: flex;
        gap: 16px;
    }

    .btn-primary-gold {
        background: var(--secondary);
        color: white;
        padding: 16px 32px;
        border-radius: var(--radio-sm);
        font-weight: 800;
        text-decoration: none;
        transition: transform 0.2s, background 0.2s;
    }

    .btn-primary-gold:hover {
        transform: translateY(-2px);
        background: #966b1a; /* Darker secondary */
    }

    .btn-secondary-white {
        background: rgba(255,255,255,0.1);
        color: white;
        padding: 16px 32px;
        border-radius: var(--radio-sm);
        font-weight: 700;
        text-decoration: none;
        border: 1px solid rgba(255,255,255,0.3);
        transition: background 0.2s;
    }

    .btn-secondary-white:hover {
        background: rgba(255,255,255,0.2);
    }

    .hero-image-container {
        position: relative;
    }

    .hero-img {
        width: 100%;
        border-radius: var(--radio-lg);
        box-shadow: 0 20px 50px rgba(0,0,0,0.4);
    }

    .categories-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 24px;
        margin-bottom: 56px;
    }

    .category-card {
        background: var(--surface);
        padding: 32px 24px;
        border-radius: var(--radio-lg);
        text-align: center;
        transition: transform 0.3s, background 0.3s, box-shadow 0.3s;
        border: 1px solid var(--surface-variant);
    }

    .category-card:hover {
        transform: translateY(-8px);
        background: var(--blanco);
        box-shadow: var(--sombra-ambient);
    }

    .category-icon {
        font-size: 36px;
        margin-bottom: 16px;
        display: block;
    }

    .category-card h3 {
        font-family: 'Noto Serif', serif;
        margin: 0;
        color: var(--primary);
        font-size: 20px;
    }

    .category-card p {
        margin: 8px 0 0;
        font-size: 14px;
        color: var(--on-surface-variant);
    }

    .benefits-strip {
        display: flex;
        justify-content: space-between;
        background: var(--surface-variant);
        padding: 32px;
        border-radius: var(--radio-lg);
        margin-bottom: 64px;
    }

    .benefit-item {
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .benefit-icon {
        font-size: 32px;
    }

    .benefit-item h4 {
        font-family: 'Noto Serif', serif;
        margin: 0;
        color: var(--primary);
        font-size: 18px;
    }

    .benefit-item p {
        margin: 4px 0 0;
        font-size: 14px;
        color: var(--on-surface-variant);
    }

    .section-head {
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        margin-bottom: 32px;
    }

    .section-head h2 {
        font-family: 'Noto Serif', serif;
        font-size: 32px;
        color: var(--primary);
        margin: 0;
    }

    .btn-link {
        color: var(--secondary);
        font-weight: 800;
        text-decoration: none;
        font-size: 14px;
    }

    .grid-products {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 32px;
        margin-bottom: 80px;
    }

    .product-card {
        background: var(--blanco);
        border-radius: var(--radio-lg);
        overflow: hidden;
        box-shadow: 0 4px 20px rgba(50, 34, 20, 0.05);
        transition: transform 0.3s, box-shadow 0.3s;
        border: 1px solid var(--surface-variant);
    }

    .product-card:hover {
        transform: translateY(-8px);
        box-shadow: var(--sombra-ambient);
    }

    .product-img {
        height: 280px;
        background: var(--surface);
        position: relative;
    }

    .product-img img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .product-tag {
        position: absolute;
        top: 16px;
        left: 16px;
        background: var(--primary);
        color: white;
        padding: 6px 12px;
        border-radius: var(--radio-sm);
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .product-info {
        padding: 24px;
    }

    .product-ref {
        font-size: 12px;
        color: var(--on-surface-variant);
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .product-info h3 {
        font-family: 'Noto Serif', serif;
        margin: 8px 0 12px;
        color: var(--primary);
        font-size: 22px;
        font-weight: 700;
    }

    .product-price {
        font-size: 26px;
        font-weight: 800;
        color: var(--secondary);
        margin-bottom: 24px;
    }

    .product-actions {
        display: flex;
        gap: 12px;
    }

    .btn-add-cart {
        width: 100%;
        background: var(--primary);
        color: white;
        border: none;
        padding: 12px;
        border-radius: var(--radio-sm);
        font-weight: 700;
        cursor: pointer;
        transition: background 0.2s;
    }

    .btn-add-cart:hover {
        background: var(--primary-container);
    }

    .btn-detail {
        background: var(--surface);
        color: var(--primary);
        text-decoration: none;
        padding: 12px 20px;
        border-radius: var(--radio-sm);
        font-weight: 700;
        transition: background 0.2s;
    }

    .btn-detail:hover {
        background: var(--surface-variant);
    }

    @media (max-width: 900px) {
        .hero-premium {
            grid-template-columns: 1fr;
            padding: 40px 24px;
            text-align: center;
        }
        .hero-premium p {
            margin-inline: auto;
        }
        .hero-buttons {
            justify-content: center;
        }
        .categories-grid {
            grid-template-columns: 1fr 1fr;
        }
        .benefits-strip {
            flex-direction: column;
            gap: 24px;
        }
        .section-head {
            flex-direction: column;
            align-items: center;
            gap: 16px;
            text-align: center;
        }
    }
</style>

@endsection
