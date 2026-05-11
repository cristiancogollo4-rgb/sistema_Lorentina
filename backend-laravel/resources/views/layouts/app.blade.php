<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Sistema Lorentina</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        :root {
            --chocolate: #5b2f2d;
            --chocolate-2: #6b3633;
            --chocolate-3: #3b1f1e;
            --vino: #5a2d2b;
            --crema-logo: #f4e5dc;
            --crema: #f7efe8;
            --crema-2: #fffaf5;
            --crema-3: #ead8ce;
            --dorado: #d6a968;
            --caramelo: #b9824f;
            --texto: #2b1a18;
            --gris: #6d5b56;
            --blanco: #ffffff;
            --sombra: 0 16px 40px rgba(91, 47, 45, 0.16);
            --sombra-fuerte: 0 24px 60px rgba(91, 47, 45, 0.25);
            --radio: 24px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: "Segoe UI", Arial, sans-serif;
            background:
                radial-gradient(circle at top left, rgba(91, 47, 45, 0.12), transparent 30%),
                linear-gradient(180deg, var(--crema-2), var(--crema));
            color: var(--texto);
        }

        a {
            text-decoration: none;
        }

        header {
            position: sticky;
            top: 0;
            z-index: 100;
            background: rgba(91, 47, 45, 0.98);
            color: white;
            padding: 14px 8%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.18);
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 13px;
        }

        .brand-logo {
            width: 54px;
            height: 54px;
            border-radius: 15px;
            object-fit: cover;
            background: var(--chocolate);
            box-shadow: 0 8px 20px rgba(0,0,0,0.16);
        }

        .brand-text {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .brand-text h1 {
            margin: 0;
            font-family: Georgia, "Times New Roman", serif;
            font-size: 28px;
            letter-spacing: 0.4px;
            color: var(--crema-logo);
            font-weight: 800;
        }

        .brand-text span {
            font-size: 12px;
            color: #f2d8cb;
            font-weight: 500;
        }

        nav {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        nav a {
            color: white;
            padding: 9px 14px;
            border-radius: 999px;
            font-weight: 700;
            font-size: 14px;
            transition: 0.25s;
        }

        nav a:hover {
            background: rgba(255,255,255,0.14);
            transform: translateY(-1px);
        }

        .nav-admin {
            background: var(--crema-logo);
            color: var(--chocolate);
        }

        .nav-admin:hover {
            background: white;
            color: var(--chocolate);
        }

        .cart-badge {
            background: var(--crema-logo);
            color: var(--chocolate);
            font-size: 12px;
            font-weight: 900;
            padding: 3px 8px;
            border-radius: 999px;
            margin-left: 5px;
        }

        .container {
            width: min(1180px, 88%);
            margin: auto;
        }

        .alert {
            background: #fff4df;
            border-left: 5px solid var(--dorado);
            padding: 14px 18px;
            border-radius: 12px;
            margin: 18px 0;
            color: var(--chocolate);
            box-shadow: 0 8px 20px rgba(0,0,0,0.05);
            font-weight: 700;
        }

        .btn {
            background: linear-gradient(135deg, var(--chocolate), var(--chocolate-2));
            color: white;
            padding: 12px 19px;
            border: none;
            border-radius: 999px;
            cursor: pointer;
            display: inline-block;
            font-weight: 800;
            transition: transform 0.2s, box-shadow 0.2s;
            box-shadow: 0 9px 22px rgba(91,47,45,0.22);
            font-size: 14px;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 13px 28px rgba(91,47,45,0.30);
        }

        .btn-outline {
            background: transparent;
            color: var(--chocolate);
            border: 1.6px solid var(--chocolate);
            box-shadow: none;
        }

        .btn-outline:hover {
            background: var(--chocolate);
            color: white;
        }

        .btn-light {
            background: var(--crema-logo);
            color: var(--chocolate);
            box-shadow: none;
        }

        .btn-light:hover {
            background: white;
        }

        .hero {
            padding: 80px 0 58px;
            display: grid;
            grid-template-columns: 1.1fr 0.9fr;
            gap: 46px;
            align-items: center;
        }

        .badge {
            display: inline-block;
            background: #ead5ca;
            color: var(--chocolate);
            padding: 8px 14px;
            border-radius: 999px;
            font-weight: 800;
            font-size: 13px;
            margin-bottom: 18px;
        }

        .hero h2 {
            font-size: clamp(38px, 5vw, 62px);
            line-height: 1.03;
            color: var(--chocolate);
            margin: 0 0 18px;
            letter-spacing: -1.5px;
        }

        .hero p {
            font-size: 18px;
            line-height: 1.75;
            color: var(--gris);
            margin-bottom: 25px;
        }

        .hero-actions {
            display: flex;
            gap: 14px;
            flex-wrap: wrap;
            align-items: center;
        }

        .hero-card {
            background: linear-gradient(145deg, #ffffff, #fff2e9);
            border-radius: 34px;
            padding: 36px;
            box-shadow: var(--sombra);
            border: 1px solid rgba(91,47,45,0.12);
            position: relative;
            overflow: hidden;
        }

        .hero-card::before {
            content: "";
            position: absolute;
            right: -65px;
            top: -65px;
            width: 180px;
            height: 180px;
            border-radius: 50%;
            background: rgba(91,47,45,0.08);
        }

        .hero-card h3 {
            font-size: 26px;
            margin-top: 0;
            color: var(--chocolate);
            position: relative;
        }

        .hero-card ul {
            padding-left: 18px;
            line-height: 1.9;
            color: var(--gris);
            font-weight: 600;
            margin-bottom: 0;
            position: relative;
        }

        .hero-logo-box {
            background: var(--chocolate);
            border-radius: 32px;
            padding: 34px;
            text-align: center;
            box-shadow: var(--sombra-fuerte);
            margin-bottom: 20px;
        }

        .hero-logo-box img {
            max-width: 100%;
            width: 360px;
            display: block;
            margin: auto;
        }

        .system-overview {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 22px;
            margin: 10px 0 55px;
        }

        .overview-card {
            background: white;
            border-radius: 24px;
            padding: 27px;
            box-shadow: var(--sombra);
            border: 1px solid rgba(91,47,45,0.08);
        }

        .overview-icon {
            width: 48px;
            height: 48px;
            border-radius: 16px;
            background: #f0ded5;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--chocolate);
            font-size: 22px;
            margin-bottom: 15px;
        }

        .overview-card h3 {
            color: var(--chocolate);
            margin: 0 0 10px;
            font-size: 22px;
        }

        .overview-card p {
            color: var(--gris);
            line-height: 1.6;
            margin: 0;
        }

        .admin-preview {
            margin: 35px 0 60px;
            background: linear-gradient(135deg, var(--chocolate), var(--chocolate-3));
            border-radius: 32px;
            padding: 38px;
            color: white;
            box-shadow: var(--sombra-fuerte);
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 28px;
            align-items: center;
            overflow: hidden;
        }

        .admin-preview h2 {
            margin: 0 0 12px;
            font-size: 33px;
        }

        .admin-preview p {
            color: #f1d9c3;
            line-height: 1.7;
            margin-bottom: 20px;
        }

        .admin-modules {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
        }

        .admin-module {
            background: rgba(255,255,255,0.12);
            border: 1px solid rgba(255,255,255,0.14);
            border-radius: 16px;
            padding: 15px;
            font-weight: 800;
            color: #fff6ee;
        }

        .section-head {
            margin: 35px 0 24px;
            display: flex;
            justify-content: space-between;
            align-items: end;
            gap: 20px;
        }

        .section-head h2 {
            margin: 0;
            color: var(--chocolate);
            font-size: 32px;
        }

        .section-head p {
            margin: 8px 0 0;
            color: var(--gris);
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(245px, 1fr));
            gap: 24px;
        }

        .card {
            background: var(--blanco);
            border-radius: var(--radio);
            overflow: hidden;
            box-shadow: var(--sombra);
            border: 1px solid rgba(91,47,45,0.08);
            transition: transform 0.25s, box-shadow 0.25s;
        }

        .card:hover {
            transform: translateY(-6px);
            box-shadow: 0 18px 42px rgba(91,47,45,0.22);
        }

        .card-img {
            position: relative;
            height: 230px;
            background: linear-gradient(135deg, #ead1c7, #fff5e9);
            overflow: hidden;
        }

        .card-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .tag {
            position: absolute;
            top: 14px;
            left: 14px;
            background: rgba(91,47,45,0.92);
            color: white;
            padding: 7px 11px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 800;
        }

        .card-content {
            padding: 20px;
        }

        .card-content h3 {
            margin: 0 0 10px;
            color: var(--chocolate);
            font-size: 21px;
        }

        .card-content p {
            color: var(--gris);
            line-height: 1.5;
        }

        .meta {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            margin: 12px 0;
        }

        .meta span {
            background: #f4e5dc;
            color: var(--chocolate);
            padding: 6px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 800;
        }

        .price-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            margin-top: 16px;
        }

        .price {
            color: var(--chocolate);
            font-weight: 900;
            font-size: 22px;
            margin: 0;
        }

        .quantity-box {
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 14px 0;
        }

        .quantity-box label {
            font-size: 13px;
            font-weight: 800;
            color: var(--chocolate);
        }

        .quantity-box input {
            width: 75px;
            padding: 9px 10px;
            border-radius: 12px;
            border: 1px solid #e2cdb8;
            background: #fffaf5;
            font-weight: 700;
            color: var(--texto);
        }

        .empty-box {
            background: white;
            padding: 35px;
            border-radius: 22px;
            box-shadow: var(--sombra);
            text-align: center;
            color: var(--gris);
            grid-column: 1 / -1;
        }

        .empty-box h3 {
            color: var(--chocolate);
            margin-top: 0;
        }

        .cart-card {
            background: white;
            border-radius: 24px;
            box-shadow: var(--sombra);
            padding: 24px;
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 720px;
        }

        th {
            background: var(--chocolate);
            color: white;
            padding: 15px;
            text-align: left;
        }

        td {
            padding: 16px;
            border-bottom: 1px solid #f0e1d2;
            color: var(--texto);
            vertical-align: middle;
        }

        .cart-img {
            width: 82px;
            height: 70px;
            border-radius: 12px;
            object-fit: cover;
            background: #f0dfcc;
        }

        .cart-quantity-form {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .cart-quantity-input {
            width: 75px;
            padding: 9px 10px;
            border-radius: 12px;
            border: 1px solid #e2cdb8;
            background: #fffaf5;
            font-weight: 700;
            color: var(--texto);
        }

        .cart-total {
            margin-top: 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #fff4e8;
            border-radius: 18px;
            padding: 20px;
            color: var(--chocolate);
        }

        .cart-total h2 {
            margin: 0;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
            gap: 18px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 7px;
            margin-bottom: 18px;
        }

        .form-group label {
            font-weight: 800;
            color: var(--chocolate);
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 13px 14px;
            border-radius: 14px;
            border: 1px solid #e2cdb8;
            outline: none;
            font-family: inherit;
            background: #fffaf5;
            color: var(--texto);
        }

        .product-detail {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 38px;
            align-items: center;
            background: white;
            border-radius: 30px;
            padding: 32px;
            box-shadow: var(--sombra);
            border: 1px solid rgba(91,47,45,0.08);
        }

        .product-detail-image {
            background: linear-gradient(135deg, #ead1c7, #fff5e9);
            border-radius: 26px;
            overflow: hidden;
            height: 520px;
            box-shadow: inset 0 0 0 1px rgba(91,47,45,0.08);
        }

        .product-detail-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .product-detail-info h1 {
            font-size: clamp(34px, 4vw, 52px);
            color: var(--chocolate);
            margin: 12px 0 16px;
            line-height: 1.05;
        }

        .detail-description {
            color: var(--gris);
            line-height: 1.7;
            font-size: 17px;
            margin-bottom: 24px;
        }

        .detail-meta {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 14px;
            margin: 22px 0;
        }

        .detail-meta div {
            background: #fff4e8;
            border-radius: 18px;
            padding: 16px;
            border: 1px solid #edd8c3;
        }

        .detail-meta strong {
            display: block;
            color: var(--chocolate);
            font-size: 13px;
            margin-bottom: 7px;
        }

        .detail-meta span {
            color: var(--gris);
            font-weight: 700;
        }

        .detail-price {
            font-size: 34px;
            font-weight: 900;
            color: var(--chocolate);
            margin: 22px 0;
        }

        .custom-pagination {
            margin: 45px 0 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .custom-pagination a,
        .custom-pagination span {
            min-width: 40px;
            height: 40px;
            padding: 0 14px;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 14px;
            border: 1.5px solid rgba(91, 47, 45, 0.22);
        }

        .custom-pagination a {
            color: var(--chocolate);
            background: white;
            box-shadow: 0 8px 18px rgba(91, 47, 45, 0.08);
            transition: 0.2s;
        }

        .custom-pagination a:hover {
            background: var(--chocolate);
            color: white;
            transform: translateY(-2px);
        }

        .custom-pagination .page-active {
            background: var(--chocolate);
            color: white;
            border-color: var(--chocolate);
            box-shadow: 0 8px 20px rgba(91, 47, 45, 0.22);
        }

        .custom-pagination .page-disabled {
            background: #efe2d9;
            color: #a58a80;
            cursor: not-allowed;
        }

        .custom-pagination .page-dots {
            border: none;
            background: transparent;
            color: var(--chocolate);
            min-width: auto;
            padding: 0 4px;
        }

        footer {
            margin-top: 70px;
            background: var(--chocolate);
            color: white;
            text-align: center;
            padding: 28px;
        }

        footer p {
            margin: 0;
            color: #f5d9bd;
        }

        @media (max-width: 900px) {
            header {
                flex-direction: column;
                gap: 14px;
            }

            nav {
                justify-content: center;
            }

            .hero,
            .admin-preview,
            .product-detail {
                grid-template-columns: 1fr;
            }

            .section-head {
                flex-direction: column;
                align-items: flex-start;
            }

            .price-row {
                flex-direction: column;
                align-items: flex-start;
            }

            .cart-quantity-form {
                flex-direction: column;
                align-items: flex-start;
            }

            .product-detail-image {
                height: 380px;
            }

            .detail-meta {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 560px) {
            .container {
                width: 92%;
            }

            .hero h2 {
                font-size: 34px;
            }

            .admin-modules {
                grid-template-columns: 1fr;
            }

            .hero-card,
            .admin-preview,
            .product-detail {
                padding: 24px;
            }

            nav a {
                font-size: 13px;
                padding: 8px 11px;
            }

            .brand-text h1 {
                font-size: 23px;
            }

            .brand-logo {
                width: 46px;
                height: 46px;
            }

            .custom-pagination a,
            .custom-pagination span {
                min-width: 36px;
                height: 36px;
                font-size: 13px;
                padding: 0 11px;
            }
        }
    </style>
</head>
<body>

<header>
    <div class="brand">
        <img class="brand-logo" src="{{ asset('images/LOGOLORENTINA.png') }}" alt="Lorentina">

        <div class="brand-text">
            <h1>Lorentina</h1>
            <span>Producción, stock y calzado inteligente</span>
        </div>
    </div>

    <nav>
        <a href="{{ route('landing') }}">Inicio</a>

        <a href="{{ route('productos.index') }}">Productos</a>

        <a href="{{ route('carrito.ver') }}">
            🛒 Carrito

            @php
                $cantidadCarrito = collect(session('carrito', []))->sum('cantidad');
            @endphp

            @if($cantidadCarrito > 0)
                <span class="cart-badge">{{ $cantidadCarrito }}</span>
            @endif
        </a>

    </nav>
</header>

@if(session('success'))
    <div class="container">
        <div class="alert">
            {{ session('success') }}
        </div>
    </div>
@endif

@yield('content')

<footer>
    <p>Sistema Lorentina – Gestión Inteligente de Producción y Stock de Calzado</p>
</footer>

</body>
</html>