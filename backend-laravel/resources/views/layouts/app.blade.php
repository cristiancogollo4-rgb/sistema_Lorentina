<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Sistema Lorentina</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        :root {
            --chocolate: #3b2418;
            --chocolate-2: #5a3826;
            --chocolate-3: #6b4031;
            --caramelo: #b9824f;
            --dorado: #d8aa67;
            --crema: #f8efe4;
            --crema-2: #fff9f2;
            --texto: #2b1a13;
            --gris: #6b5b52;
            --blanco: #ffffff;
            --sombra: 0 14px 35px rgba(59, 36, 24, 0.14);
            --sombra-fuerte: 0 22px 55px rgba(59, 36, 24, 0.22);
            --radio: 22px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: "Segoe UI", Arial, sans-serif;
            background:
                radial-gradient(circle at top left, rgba(216, 170, 103, 0.18), transparent 35%),
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
            background: rgba(59, 36, 24, 0.97);
            color: white;
            padding: 15px 8%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 7px 24px rgba(0, 0, 0, 0.18);
        }

        .brand {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .brand h1 {
            margin: 0;
            font-size: 25px;
            letter-spacing: 0.5px;
            font-weight: 800;
        }

        .brand span {
            font-size: 12px;
            color: #f0d4b6;
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
            background: #f3ddc3;
            color: var(--chocolate);
        }

        .nav-admin:hover {
            background: white;
            color: var(--chocolate);
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
            box-shadow: 0 8px 20px rgba(59,36,24,0.20);
            font-size: 14px;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 25px rgba(59,36,24,0.26);
        }

        .btn-outline {
            background: transparent;
            color: var(--chocolate);
            border: 1.5px solid var(--chocolate);
            box-shadow: none;
        }

        .btn-outline:hover {
            background: var(--chocolate);
            color: white;
        }

        .btn-light {
            background: white;
            color: var(--chocolate);
            box-shadow: none;
        }

        .hero {
            padding: 80px 0 55px;
            display: grid;
            grid-template-columns: 1.15fr 0.85fr;
            gap: 42px;
            align-items: center;
        }

        .badge {
            display: inline-block;
            background: #f0d7bc;
            color: var(--chocolate);
            padding: 8px 14px;
            border-radius: 999px;
            font-weight: 800;
            font-size: 13px;
            margin-bottom: 18px;
        }

        .hero h2 {
            font-size: clamp(36px, 5vw, 58px);
            line-height: 1.05;
            color: var(--chocolate);
            margin: 0 0 18px;
            letter-spacing: -1.4px;
        }

        .hero p {
            font-size: 18px;
            line-height: 1.7;
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
            background: linear-gradient(145deg, white, #fff3e5);
            border-radius: 32px;
            padding: 34px;
            box-shadow: var(--sombra);
            border: 1px solid rgba(185,130,79,0.25);
            position: relative;
            overflow: hidden;
        }

        .hero-card h3 {
            font-size: 26px;
            margin-top: 0;
            color: var(--chocolate);
        }

        .hero-card ul {
            padding-left: 18px;
            line-height: 1.9;
            color: var(--gris);
            font-weight: 600;
            margin-bottom: 0;
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
            border: 1px solid rgba(90,56,38,0.08);
        }

        .overview-icon {
            width: 48px;
            height: 48px;
            border-radius: 16px;
            background: #f2dfca;
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
            border-radius: 30px;
            padding: 35px;
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
            font-size: 32px;
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
            border: 1px solid rgba(90,56,38,0.08);
            transition: transform 0.25s, box-shadow 0.25s;
        }

        .card:hover {
            transform: translateY(-6px);
            box-shadow: 0 18px 38px rgba(59, 36, 24, 0.20);
        }

        .card-img {
            position: relative;
            height: 230px;
            background: linear-gradient(135deg, #ead1b7, #fff5e9);
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
            background: rgba(59,36,24,0.90);
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
            background: #f6eadc;
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

        .pagination {
            margin: 30px 0;
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
            border: 1px solid rgba(90,56,38,0.08);
        }

        .product-detail-image {
            background: linear-gradient(135deg, #ead1b7, #fff5e9);
            border-radius: 26px;
            overflow: hidden;
            height: 520px;
            box-shadow: inset 0 0 0 1px rgba(90,56,38,0.08);
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
        }
    </style>
</head>
<body>

<header>
    <div class="brand">
        <h1>Sistema Lorentina</h1>
        <span>Producción, stock y calzado inteligente</span>
    </div>

    <nav>
        <a href="{{ route('landing') }}">Inicio</a>
        <a href="{{ route('productos.index') }}">Productos</a>
        <a href="{{ route('carrito.ver') }}">Carrito</a>
        <a href="http://localhost:5173" target="_blank" class="nav-admin">Panel Admin</a>
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