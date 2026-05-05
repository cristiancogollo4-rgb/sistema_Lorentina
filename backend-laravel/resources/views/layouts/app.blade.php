<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Sistema Lorentina</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        :root {
            --chocolate: #4b2e1f;
            --chocolate-claro: #7a4a32;
            --crema: #f8f1e9;
            --dorado: #c89b5c;
            --oscuro: #24140f;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: var(--crema);
            color: var(--oscuro);
        }

        header {
            background: var(--chocolate);
            color: white;
            padding: 18px 8%;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        header h1 {
            margin: 0;
            font-size: 24px;
        }

        nav a {
            color: white;
            margin-left: 18px;
            text-decoration: none;
            font-weight: bold;
        }

        .container {
            width: 84%;
            margin: auto;
        }

        .hero {
            padding: 70px 0;
            display: grid;
            grid-template-columns: 1.2fr 1fr;
            gap: 35px;
            align-items: center;
        }

        .hero h2 {
            font-size: 44px;
            color: var(--chocolate);
            margin-bottom: 10px;
        }

        .hero p {
            font-size: 18px;
            line-height: 1.6;
        }

        .hero-box {
            background: white;
            border-radius: 25px;
            padding: 35px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
        }

        .btn {
            background: var(--chocolate);
            color: white;
            padding: 11px 17px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-weight: bold;
        }

        .btn:hover {
            background: var(--chocolate-claro);
        }

        .btn-outline {
            background: transparent;
            border: 1px solid var(--chocolate);
            color: var(--chocolate);
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
            gap: 22px;
        }

        .card {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 8px 22px rgba(0,0,0,0.08);
        }

        .card img {
            width: 100%;
            height: 210px;
            object-fit: cover;
            background: #ddd;
        }

        .card-content {
            padding: 18px;
        }

        .price {
            color: var(--chocolate);
            font-weight: bold;
            font-size: 20px;
        }

        .alert {
            background: #efe1d2;
            padding: 12px;
            border-radius: 8px;
            margin: 15px 0;
            color: var(--chocolate);
        }

        table {
            width: 100%;
            background: white;
            border-collapse: collapse;
            border-radius: 12px;
            overflow: hidden;
        }

        th, td {
            padding: 14px;
            border-bottom: 1px solid #eee;
            text-align: left;
        }

        th {
            background: var(--chocolate);
            color: white;
        }

        footer {
            margin-top: 50px;
            background: var(--chocolate);
            color: white;
            text-align: center;
            padding: 25px;
        }

        .pagination {
            margin-top: 25px;
        }

        @media (max-width: 800px) {
            .hero {
                grid-template-columns: 1fr;
            }

            header {
                flex-direction: column;
                gap: 12px;
            }

            .hero h2 {
                font-size: 34px;
            }
        }
    </style>
</head>
<body>

<header>
    <h1>Sistema Lorentina</h1>

    <nav>
        <a href="{{ route('landing') }}">Inicio</a>
        <a href="{{ route('productos.index') }}">Productos</a>
        <a href="{{ route('carrito.ver') }}">Carrito</a>
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