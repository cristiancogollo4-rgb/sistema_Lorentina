@extends('layouts.app')

@section('content')
@php
    $whatsappNumber = config('services.lorentina.whatsapp_number', '573000000000');
    $whatsappMayoristas = 'https://wa.me/' . $whatsappNumber . '?text=' . rawurlencode('Hola Lorentina, quiero información para comprar al por mayor.');
    $whatsappContacto = 'https://wa.me/' . $whatsappNumber . '?text=' . rawurlencode('Hola Lorentina, quiero recibir asesoría sobre el catálogo.');
    $socialLinks = [
        ['label' => 'Instagram', 'abbr' => 'IG', 'url' => config('services.lorentina.instagram_url')],
        ['label' => 'Facebook', 'abbr' => 'FB', 'url' => config('services.lorentina.facebook_url')],
        ['label' => 'TikTok', 'abbr' => 'TT', 'url' => config('services.lorentina.tiktok_url')],
    ];

    $videoSandalias = [
        [
            'title' => '1006 Perla',
            'subtitle' => 'Un básico luminoso para todos los días.',
            'src' => asset('videos/sandalia-1006-perla.mp4'),
            'href' => route('productos.index', ['search' => '1006 Perla']),
        ],
        [
            'title' => '1028 Melao Nata',
            'subtitle' => 'Contraste suave con aire artesanal.',
            'src' => asset('videos/sandalia-1028-melao.mp4'),
            'href' => route('productos.index', ['search' => '1028 Melao']),
        ],
        [
            'title' => '1056 Blanco',
            'subtitle' => 'Línea limpia y fácil de combinar.',
            'src' => asset('videos/sandalia-1056-blanco.mp4'),
            'href' => route('productos.index', ['search' => '1056 Blanco']),
        ],
        [
            'title' => '1187 Plata',
            'subtitle' => 'Brillo sutil para elevar el look.',
            'src' => asset('videos/sandalia-1187-plata.mp4'),
            'href' => route('productos.index', ['search' => '1187 Plata']),
        ],
        [
            'title' => 'Zaras Negro',
            'subtitle' => 'Altura, presencia y comodidad.',
            'src' => asset('videos/zaras-negro.mp4'),
            'href' => route('productos.index', ['tipo' => 'plataforma', 'search' => 'Negro']),
        ],
    ];
@endphp

<div class="ecommerce-home">
    <section class="hero-premium">
        <div class="hero-content">
            <span class="hero-badge">Directo de fábrica</span>
            <h1>Calzado hecho en Bucaramanga para caminar bonito y cómodo</h1>
            <p>Sandalias y calzado Lorentina con diseño artesanal, materiales seleccionados y disponibilidad real para comprar al detal o al por mayor.</p>
            <div class="hero-buttons">
                <a href="{{ route('productos.index') }}" class="btn-primary-gold">Ver disponibles hoy</a>
                <a href="{{ $whatsappMayoristas }}" class="btn-secondary-white" target="_blank" rel="noopener">Comprar por WhatsApp</a>
            </div>

            <form action="{{ route('productos.index') }}" method="GET" class="hero-search">
                <input type="text" name="search" placeholder="Busca por referencia, color o estilo">
                <button type="submit">Buscar</button>
            </form>
        </div>
        <div class="hero-image-container">
            <img src="{{ asset('images/hero-lorentina-comunidad.jpg') }}" alt="Lorentina mostrando sandalias de la fábrica" class="hero-img">
        </div>
    </section>

    <section class="trust-strip" aria-label="Beneficios de comprar en Lorentina">
        <article>
            <span>CO</span>
            <div>
                <h3>Hecho en Bucaramanga</h3>
                <p>Producción local con control de fábrica.</p>
            </div>
        </article>
        <article>
            <span>TR</span>
            <div>
                <h3>Suela flexible</h3>
                <p>Ligereza y resistencia para uso diario.</p>
            </div>
        </article>
        <article>
            <span>24h</span>
            <div>
                <h3>Compra asistida</h3>
                <p>Te ayudamos por WhatsApp con talla y disponibilidad.</p>
            </div>
        </article>
        <article>
            <span>+</span>
            <div>
                <h3>Canal mayorista</h3>
                <p>Opciones para tiendas y distribuidores.</p>
            </div>
        </article>
    </section>

    <section class="categories-grid" aria-label="Categorías principales">
        <a href="{{ route('productos.index', ['tipo' => 'clasica']) }}" class="category-card">
            <div class="category-icon">Cl</div>
            <h3>Clásicas</h3>
            <p>Diseños versátiles para todos los días</p>
        </a>
        <a href="{{ route('productos.index', ['tipo' => 'romana']) }}" class="category-card">
            <div class="category-icon">Ro</div>
            <h3>Romanas</h3>
            <p>Tiras, amarre y presencia artesanal</p>
        </a>
        <a href="{{ route('productos.index', ['tipo' => 'plataforma']) }}" class="category-card">
            <div class="category-icon">Za</div>
            <h3>Zaras</h3>
            <p>Altura cómoda con estilo moderno</p>
        </a>
        <a href="{{ $whatsappMayoristas }}" target="_blank" rel="noopener" class="category-card">
            <div class="category-icon">Ma</div>
            <h3>Mayoristas</h3>
            <p>Catálogo y atención para tiendas</p>
        </a>
    </section>

    <section class="video-feature">
        <div class="video-copy">
            <span class="section-kicker">Comodidad que se nota</span>
            <h2>Nuestra suela TR</h2>
            <p>La suela es una de las razones por las que una sandalia se siente bien desde el primer uso. Este video explica por qué Lorentina usa suela TR en modelos pensados para caminar con ligereza, flexibilidad y resistencia.</p>
            <a href="{{ route('productos.index') }}" class="btn-primary-gold">Ver modelos con suela TR</a>
        </div>
        <div class="video-frame">
            <video controls preload="metadata" playsinline poster="{{ asset('images/hero-lorentina.jpg') }}">
                <source src="{{ asset('videos/suela-tr.mp4') }}" type="video/mp4">
                Tu navegador no puede reproducir este video.
            </video>
        </div>
    </section>

    <div class="section-head" id="novedades">
        <div>
            <span class="section-kicker">Selección de fábrica</span>
            <h2>Modelos destacados</h2>
            <p>Las 4 sandalias más vendidas de la semana, con disponibilidad para comprar ahora o pedir asesoría por WhatsApp.</p>
        </div>
        <a href="{{ route('productos.index') }}" class="btn-link">Ver todo el catálogo</a>
    </div>

    <div class="grid-products">
        @forelse($productos as $producto)
            @php
                $imagenes = $producto->todas_las_imagenes_src;
                $hasMultiple = count($imagenes) > 1;
            @endphp
            <article class="product-card"
                 data-images="{{ json_encode($imagenes) }}"
                 onmouseenter="startCarousel(this)"
                 onmouseleave="stopCarousel(this)">
                <div class="product-img">
                    <span class="product-tag">{{ $producto->tipo ?? 'Nuevo' }}</span>
                    <img src="{{ $imagenes[0] }}" alt="{{ $producto->nombre_modelo }}" class="main-img" loading="lazy">
                    @if($hasMultiple)
                        <div class="carousel-dots">
                            @foreach($imagenes as $idx => $img)
                                <span class="dot {{ $idx == 0 ? 'active' : '' }}"></span>
                            @endforeach
                        </div>
                    @endif
                </div>
                <div class="product-info">
                    <span class="product-ref">Ref: {{ $producto->referencia ?? 'LR' }}</span>
                    <h3>{{ $producto->nombre_modelo }}</h3>
                    <p class="product-price">${{ number_format($producto->precio_detal, 0, ',', '.') }}</p>
                    @if(! empty($producto->total_vendido_semana))
                        <p class="product-sales">{{ (int) $producto->total_vendido_semana }} pares vendidos esta semana</p>
                    @endif
                    <a href="{{ route('productos.show', $producto->id) }}" class="btn btn-primary product-cta">Ver producto</a>
                </div>
            </article>
        @empty
            <div class="empty-state">
                <p>Próximamente nuevos modelos disponibles.</p>
            </div>
        @endforelse
    </div>

    <section class="motion-section">
        <div class="section-head compact">
            <div>
                <span class="section-kicker">Míralas en movimiento</span>
                <h2>Sandalias reales, detalles reales</h2>
                <p>Videos cortos para ver caída, color, brillo y forma antes de elegir.</p>
            </div>
        </div>

        <div class="video-carousel">
            @foreach($videoSandalias as $video)
                <article class="motion-card">
                    <video muted loop playsinline preload="metadata">
                        <source src="{{ $video['src'] }}" type="video/mp4">
                    </video>
                    <div class="motion-card-info">
                        <div>
                            <h3>{{ $video['title'] }}</h3>
                            <p>{{ $video['subtitle'] }}</p>
                        </div>
                        <a href="{{ $video['href'] }}">Ver</a>
                    </div>
                </article>
            @endforeach
        </div>
    </section>

    <section class="education-grid">
        <article class="education-card">
            <div class="video-frame">
                <video controls preload="metadata" playsinline poster="{{ asset('images/LOGOLORENTINA.png') }}">
                    <source src="{{ asset('videos/mitos-cuero.mp4') }}" type="video/mp4">
                    Tu navegador no puede reproducir este video.
                </video>
            </div>
            <div>
                <span class="section-kicker">Guía de compra</span>
                <h2>5 mitos sobre el cuero</h2>
                <p>Un contenido pensado para comprar con más criterio: cuidado, resistencia, textura y lo que debes revisar al elegir calzado.</p>
            </div>
        </article>

        <article class="education-card mayoristas-card">
            <div>
                <span class="section-kicker">Para tiendas</span>
                <h2>Compra Lorentina al por mayor</h2>
                <p>Si tienes boutique, local o vendes por catálogo, este canal te muestra cómo trabajamos referencias, colores y asesoría para mayoristas.</p>
                <a href="{{ $whatsappMayoristas }}" class="btn-primary-gold" target="_blank" rel="noopener">Solicitar información</a>
            </div>
            <div class="video-frame">
                <video controls preload="metadata" playsinline poster="{{ asset('images/hero-lorentina.jpg') }}">
                    <source src="{{ asset('videos/mayoristas-web.mp4') }}" type="video/mp4">
                    Tu navegador no puede reproducir este video.
                </video>
            </div>
        </article>
    </section>

    <section class="contact-section" aria-label="Información y contacto">
        <div class="contact-main">
            <span class="section-kicker">Información y contacto</span>
            <h2>Hablemos de tus próximas Lorentina</h2>
            <p>Te acompañamos para elegir talla, revisar disponibilidad, coordinar envíos o resolver compras al por mayor directamente con la fábrica.</p>
            <div class="contact-actions">
                <a href="{{ $whatsappContacto }}" class="btn-primary-gold" target="_blank" rel="noopener">Escribir por WhatsApp</a>
                <a href="mailto:{{ config('services.lorentina.email') }}" class="btn-outline-contact">Enviar correo</a>
            </div>
        </div>

        <div class="contact-details">
            <div class="contact-item">
                <span>WA</span>
                <div>
                    <h3>WhatsApp</h3>
                    <a href="{{ $whatsappContacto }}" target="_blank" rel="noopener">+{{ $whatsappNumber }}</a>
                </div>
            </div>
            <div class="contact-item">
                <span>CO</span>
                <div>
                    <h3>Ubicación</h3>
                    <p>{{ config('services.lorentina.city') }}</p>
                </div>
            </div>
            <div class="contact-item">
                <span>@</span>
                <div>
                    <h3>Correo</h3>
                    <a href="mailto:{{ config('services.lorentina.email') }}">{{ config('services.lorentina.email') }}</a>
                </div>
            </div>
            <div class="social-row" aria-label="Redes sociales">
                @foreach($socialLinks as $social)
                    @if($social['url'])
                        <a href="{{ $social['url'] }}" target="_blank" rel="noopener" aria-label="{{ $social['label'] }}">
                            <span>{{ $social['abbr'] }}</span>
                            {{ $social['label'] }}
                        </a>
                    @endif
                @endforeach
            </div>
        </div>
    </section>
</div>

<style>
    .ecommerce-home {
        padding: 24px 0 20px;
        width: min(1180px, 88%);
        max-width: 100%;
        margin: 0 auto;
        overflow-x: hidden;
    }

    .hero-premium {
        display: grid;
        grid-template-columns: minmax(0, 1.05fr) minmax(320px, 0.95fr);
        gap: 40px;
        align-items: center;
        background: var(--primary-container);
        border-radius: var(--radio-lg);
        padding: clamp(28px, 5vw, 60px);
        color: white;
        margin-bottom: 28px;
        overflow: hidden;
        box-shadow: var(--sombra-ambient);
        max-width: 100%;
    }

    .hero-content {
        z-index: 2;
        min-width: 0;
    }

    .hero-badge,
    .section-kicker {
        background: var(--secondary);
        color: white;
        padding: 6px 12px;
        border-radius: var(--radio-sm);
        font-weight: 800;
        font-size: 12px;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        display: inline-flex;
        margin-bottom: 16px;
    }

    .section-kicker {
        background: #efe2d2;
        color: var(--secondary);
    }

    .hero-premium h1,
    .video-copy h2,
    .section-head h2,
    .education-card h2 {
        font-family: 'Noto Serif', serif;
        color: var(--primary);
    }

    .hero-premium h1 {
        font-size: clamp(38px, 5vw, 58px);
        line-height: 1.05;
        margin: 0 0 22px;
        color: var(--blanco);
        font-weight: 700;
    }

    .hero-premium p {
        font-size: 18px;
        color: var(--surface);
        margin-bottom: 28px;
        max-width: 560px;
        opacity: 0.92;
    }

    .hero-buttons {
        display: flex;
        gap: 14px;
        flex-wrap: wrap;
    }

    .btn-primary-gold,
    .btn-secondary-white {
        display: inline-flex;
        justify-content: center;
        align-items: center;
        min-height: 48px;
        padding: 13px 24px;
        border-radius: var(--radio-sm);
        font-weight: 800;
        text-decoration: none;
        transition: transform 0.2s, background 0.2s;
    }

    .btn-primary-gold {
        background: var(--secondary);
        color: white;
    }

    .btn-primary-gold:hover,
    .btn-secondary-white:hover {
        transform: translateY(-2px);
    }

    .btn-secondary-white {
        background: rgba(255, 255, 255, 0.1);
        color: white;
        border: 1px solid rgba(255, 255, 255, 0.32);
    }

    .hero-search {
        display: flex;
        gap: 0;
        background: white;
        padding: 5px;
        border-radius: 999px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.12);
        margin-top: 32px;
        max-width: 520px;
    }

    .hero-search input {
        flex: 1;
        min-width: 0;
        border: none;
        padding: 12px 20px;
        border-radius: 999px 0 0 999px;
        outline: none;
        font-size: 1rem;
        color: #333;
    }

    .hero-search button {
        background: var(--secondary);
        color: white;
        border: none;
        padding: 12px 22px;
        border-radius: 999px;
        cursor: pointer;
        font-weight: 800;
    }

    .hero-image-container {
        position: relative;
        min-width: 0;
    }

    .hero-img {
        width: 100%;
        aspect-ratio: 4 / 5;
        object-fit: cover;
        object-position: center top;
        border-radius: var(--radio-lg);
        box-shadow: 0 20px 50px rgba(0, 0, 0, 0.36);
    }

    .trust-strip {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 14px;
        margin-bottom: 34px;
    }

    .trust-strip article,
    .category-card,
    .product-card,
    .motion-card,
    .education-card,
    .video-feature {
        background: var(--blanco);
        border: 1px solid var(--surface-variant);
        box-shadow: 0 4px 20px rgba(50, 34, 20, 0.05);
    }

    .trust-strip article {
        display: flex;
        gap: 12px;
        align-items: center;
        border-radius: var(--radio-lg);
        padding: 18px;
    }

    .trust-strip span {
        display: grid;
        place-items: center;
        flex: 0 0 42px;
        height: 42px;
        border-radius: 50%;
        background: #efe2d2;
        color: var(--secondary);
        font-weight: 900;
        font-size: 13px;
    }

    .trust-strip h3,
    .trust-strip p {
        margin: 0;
    }

    .trust-strip h3 {
        color: var(--primary);
        font-size: 15px;
    }

    .trust-strip p {
        color: var(--on-surface-variant);
        font-size: 13px;
        line-height: 1.45;
    }

    .categories-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 20px;
        margin-bottom: 42px;
    }

    .category-card {
        display: block;
        padding: 26px 20px;
        border-radius: var(--radio-lg);
        text-align: left;
        transition: transform 0.25s, box-shadow 0.25s;
    }

    .category-card:hover,
    .product-card:hover,
    .motion-card:hover {
        transform: translateY(-6px);
        box-shadow: var(--sombra-ambient);
    }

    .category-icon {
        display: grid;
        place-items: center;
        width: 48px;
        height: 48px;
        border-radius: 50%;
        margin-bottom: 16px;
        background: var(--primary);
        color: white;
        font-weight: 900;
    }

    .category-card h3 {
        font-family: 'Noto Serif', serif;
        margin: 0;
        color: var(--primary);
        font-size: 21px;
    }

    .category-card p {
        margin: 8px 0 0;
        color: var(--on-surface-variant);
        font-size: 14px;
    }

    .video-feature {
        display: grid;
        grid-template-columns: 0.9fr 1.1fr;
        gap: 28px;
        align-items: center;
        border-radius: var(--radio-lg);
        padding: clamp(22px, 4vw, 36px);
        margin-bottom: 56px;
        min-width: 0;
    }

    .video-copy,
    .video-frame,
    .education-card > div,
    .motion-card,
    .product-card {
        min-width: 0;
    }

    .video-copy h2,
    .education-card h2 {
        margin: 0 0 14px;
        font-size: clamp(30px, 4vw, 42px);
        line-height: 1.12;
    }

    .video-copy p,
    .education-card p,
    .section-head p,
    .motion-card-info p {
        color: var(--on-surface-variant);
    }

    .video-frame {
        overflow: hidden;
        border-radius: var(--radio-lg);
        background: #17120d;
    }

    .video-frame video {
        display: block;
        width: 100%;
        max-height: 620px;
        background: #17120d;
    }

    .section-head {
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        gap: 20px;
        margin-bottom: 28px;
    }

    .section-head.compact {
        margin-bottom: 20px;
    }

    .section-head h2 {
        font-size: 34px;
        margin: 0;
    }

    .section-head p {
        margin: 8px 0 0;
    }

    .btn-link {
        color: var(--secondary);
        font-weight: 800;
        text-decoration: none;
    }

    .grid-products {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
        gap: 28px;
        margin-bottom: 66px;
    }

    .product-card {
        border-radius: var(--radio-lg);
        overflow: hidden;
        transition: transform 0.25s, box-shadow 0.25s;
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
        z-index: 3;
    }

    .product-info {
        padding: 22px;
    }

    .product-ref {
        font-size: 12px;
        color: var(--on-surface-variant);
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .product-info h3 {
        font-family: 'Noto Serif', serif;
        margin: 8px 0 10px;
        color: var(--primary);
        font-size: 22px;
    }

    .product-price {
        font-size: 24px;
        font-weight: 900;
        color: var(--secondary);
        margin: 0 0 10px;
    }

    .product-sales {
        margin: 0 0 18px;
        color: var(--on-surface-variant);
        font-size: 13px;
        font-weight: 800;
    }

    .product-cta {
        width: 100%;
    }

    .motion-section {
        margin-bottom: 62px;
    }

    .video-carousel {
        display: flex;
        gap: 18px;
        overflow-x: auto;
        max-width: 100%;
        padding-bottom: 8px;
        scroll-snap-type: x proximity;
    }

    .motion-card {
        flex: 0 0 min(220px, 72vw);
        border-radius: var(--radio-lg);
        overflow: hidden;
        transition: transform 0.25s, box-shadow 0.25s;
        scroll-snap-align: start;
    }

    .motion-card video {
        display: block;
        width: 100%;
        aspect-ratio: 9 / 13;
        object-fit: cover;
        background: #17120d;
    }

    .motion-card-info {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        padding: 15px;
    }

    .motion-card-info h3 {
        margin: 0 0 4px;
        color: var(--primary);
        font-size: 16px;
    }

    .motion-card-info p {
        margin: 0;
        font-size: 12px;
        line-height: 1.4;
    }

    .motion-card-info a {
        flex: 0 0 auto;
        color: var(--secondary);
        font-weight: 900;
    }

    .education-grid {
        display: grid;
        gap: 24px;
        margin-bottom: 30px;
    }

    .education-card {
        display: grid;
        grid-template-columns: 0.85fr 1fr;
        gap: 28px;
        align-items: center;
        border-radius: var(--radio-lg);
        padding: clamp(18px, 3vw, 28px);
    }

    .mayoristas-card {
        grid-template-columns: 1fr 0.85fr;
        background: var(--primary-container);
        color: white;
    }

    .mayoristas-card h2 {
        color: white;
    }

    .mayoristas-card p {
        color: var(--surface);
    }

    .empty-state {
        grid-column: 1 / -1;
        background: white;
        border-radius: var(--radio-lg);
        padding: 28px;
        text-align: center;
    }

    .contact-section {
        display: grid;
        grid-template-columns: 1.05fr 0.95fr;
        gap: 28px;
        align-items: stretch;
        background: var(--primary);
        color: white;
        border-radius: var(--radio-lg);
        padding: clamp(24px, 4vw, 40px);
        margin: 58px 0 20px;
        box-shadow: var(--sombra-ambient);
    }

    .contact-main,
    .contact-details {
        min-width: 0;
    }

    .contact-main h2 {
        font-family: 'Noto Serif', serif;
        color: white;
        font-size: clamp(30px, 4vw, 44px);
        line-height: 1.1;
        margin: 0 0 14px;
    }

    .contact-main p {
        color: #efe2d2;
        max-width: 560px;
        margin: 0 0 24px;
    }

    .contact-actions {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
    }

    .btn-outline-contact {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 48px;
        padding: 13px 24px;
        border-radius: var(--radio-sm);
        border: 1px solid rgba(255, 255, 255, 0.36);
        color: white;
        font-weight: 800;
    }

    .contact-details {
        display: grid;
        gap: 14px;
    }

    .contact-item {
        display: flex;
        align-items: center;
        gap: 14px;
        background: rgba(255, 255, 255, 0.08);
        border: 1px solid rgba(255, 255, 255, 0.12);
        border-radius: var(--radio-lg);
        padding: 16px;
    }

    .contact-item > span,
    .social-row span {
        display: grid;
        place-items: center;
        flex: 0 0 42px;
        height: 42px;
        border-radius: 50%;
        background: #efe2d2;
        color: var(--primary);
        font-size: 13px;
        font-weight: 900;
    }

    .contact-item h3,
    .contact-item p {
        margin: 0;
    }

    .contact-item h3 {
        color: white;
        font-size: 15px;
    }

    .contact-item p,
    .contact-item a {
        color: #efe2d2;
        font-weight: 700;
    }

    .social-row {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 12px;
    }

    .social-row a {
        display: flex;
        align-items: center;
        gap: 10px;
        min-width: 0;
        background: white;
        color: var(--primary);
        border-radius: var(--radio-lg);
        padding: 12px;
        font-weight: 900;
    }

    @media (max-width: 980px) {
        .hero-premium,
        .video-feature,
        .education-card,
        .mayoristas-card,
        .contact-section {
            grid-template-columns: 1fr;
        }

        .trust-strip,
        .categories-grid {
            grid-template-columns: repeat(2, 1fr);
        }

        .video-carousel {
            margin-right: -6%;
            padding-right: 6%;
        }
    }

    @media (max-width: 640px) {
        .ecommerce-home {
            padding-top: 14px;
        }

        .hero-premium {
            gap: 24px;
        }

        .hero-buttons,
        .hero-search,
        .section-head,
        .contact-actions {
            flex-direction: column;
            align-items: stretch;
        }

        .hero-search {
            border-radius: var(--radio-lg);
        }

        .hero-search input,
        .hero-search button {
            border-radius: var(--radio-sm);
        }

        .hero-img {
            aspect-ratio: 4 / 3;
        }

        .trust-strip,
        .categories-grid {
            grid-template-columns: 1fr;
        }

        .grid-products {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
        }

        .product-img {
            height: 160px;
        }

        .product-info {
            padding: 14px;
        }

        .product-info h3 {
            font-size: 16px;
        }

        .product-price {
            font-size: 18px;
        }

        .video-carousel {
            margin-right: -4%;
            padding-right: 4%;
        }

        .social-row {
            grid-template-columns: 1fr;
        }
    }
</style>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const motionVideos = document.querySelectorAll('.motion-card video');

        const playVideo = (video) => {
            video.play().catch(() => {});
        };

        const pauseVideo = (video) => {
            video.pause();
            video.currentTime = 0;
        };

        if ('IntersectionObserver' in window) {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        playVideo(entry.target);
                    } else {
                        pauseVideo(entry.target);
                    }
                });
            }, { threshold: 0.45 });

            motionVideos.forEach((video) => observer.observe(video));
            return;
        }

        motionVideos.forEach((video) => {
            video.addEventListener('mouseenter', () => playVideo(video));
            video.addEventListener('mouseleave', () => pauseVideo(video));
        });
    });
</script>
@endpush
@endsection
