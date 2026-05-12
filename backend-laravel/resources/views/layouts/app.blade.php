<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Lorentina - Calzado Artesanal de Bucaramanga</title>
    <meta name="description" content="Descubre la mejor selección de sandalias y calzado artesanal en Lorentina. Calidad premium directamente desde la fábrica en Bucaramanga.">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Fonts from Stitch Design System -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;600;800&family=Noto+Serif:ital,wght@0,400;0,700;1,400&display=swap"
        rel="stylesheet">

    <style>
        :root {
            --primary: #322214;
            /* Saddle Brown */
            --primary-container: #4a3728;
            --secondary: #7f5614;
            /* Burnished Gold */
            --secondary-container: #fdc579;
            --tertiary: #481300;
            /* Terracotta */
            --background: #fbf9f4;
            /* Parchment */
            --surface: #f0eee9;
            --surface-variant: #e4e2dd;
            --on-surface: #1b1c19;
            --on-surface-variant: #4e453e;
            --outline: #80756d;
            --blanco: #ffffff;
            --radio-sm: 4px;
            --radio-lg: 12px;
            --sombra-ambient: 0 8px 30px rgba(50, 34, 20, 0.08);
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: 'Manrope', sans-serif;
            background-color: var(--background);
            color: var(--on-surface);
            line-height: 1.6;
        }

        a {
            text-decoration: none;
        }

        header {
            position: sticky;
            top: 0;
            z-index: 100;
            background: var(--primary);
            color: white;
            padding: 16px 8%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
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
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.16);
        }

        .brand-text {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .brand-text h1 {
            margin: 0;
            font-family: 'Noto Serif', serif;
            font-size: 28px;
            letter-spacing: -0.02em;
            color: #fbf9f4;
            font-weight: 700;
        }

        .brand-text span {
            font-size: 12px;
            color: #f2d8cb;
            font-weight: 500;
        }

        .filter-bar {
            background: white;
            padding: 24px;
            border-radius: var(--radio);
            box-shadow: var(--sombra);
            margin-bottom: 30px;
            border: 1px solid rgba(91,47,45,0.05);
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .filter-label {
            font-size: 13px;
            font-weight: 800;
            color: var(--chocolate);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .filter-options {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .size-tag.active {
            background: var(--primary) !important;
            color: white !important;
            box-shadow: 0 4px 10px rgba(50, 34, 20, 0.2);
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
            background: rgba(255, 255, 255, 0.14);
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
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.05);
            font-weight: 700;
        }

        .btn {
            background: var(--primary);
            color: white !important;
            padding: 12px 24px;
            border: none;
            border-radius: 999px;
            cursor: pointer;
            display: inline-block;
            font-weight: 800;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(50, 34, 20, 0.2);
            font-size: 14px;
            text-align: center;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(50, 34, 20, 0.3);
            filter: brightness(1.2);
        }

        .btn-outline {
            background: white;
            color: var(--primary) !important;
            border: 2px solid var(--primary);
            box-shadow: none;
        }

        .btn-outline:hover {
            background: var(--primary);
            color: white !important;
        }

        /* Size Selector Styles */
        .size-option input:checked + .size-btn {
            background: var(--primary) !important;
            color: white !important;
            border-color: var(--primary) !important;
            box-shadow: 0 4px 10px rgba(50, 34, 20, 0.2);
        }

        .size-btn:hover {
            border-color: var(--primary) !important;
            color: var(--primary);
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
            border: 1px solid rgba(91, 47, 45, 0.12);
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
            background: rgba(91, 47, 45, 0.08);
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
            border: 1px solid rgba(91, 47, 45, 0.08);
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
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.14);
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
            border: 1px solid rgba(91, 47, 45, 0.08);
            transition: transform 0.25s, box-shadow 0.25s;
        }

        .card:hover {
            transform: translateY(-6px);
            box-shadow: 0 18px 42px rgba(91, 47, 45, 0.22);
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
            background: rgba(91, 47, 45, 0.92);
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

        .carrito-empty-btn {
            display: inline-block;
            background: #ffffff !important;
            color: #000000 !important;
            border: 1px solid #e2cdb8 !important;
            padding: 12px 28px;
            border-radius: 999px;
            font-weight: 800;
            box-shadow: 0 8px 18px rgba(50, 34, 20, 0.12);
        }

        .carrito-empty-btn:hover {
            background: #f4e5dc !important;
            color: #000000 !important;
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
            border: 1px solid rgba(91, 47, 45, 0.08);
        }

        .product-detail-image {
            background: linear-gradient(135deg, #ead1c7, #fff5e9);
            border-radius: 26px;
            overflow: hidden;
            height: 520px;
            box-shadow: inset 0 0 0 1px rgba(91, 47, 45, 0.08);
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

        .filter-bar {
            margin-bottom: 2rem;
            display: flex;
            gap: 0.8rem;
            flex-wrap: wrap;
            padding: 0.5rem 0;
            border-bottom: 1px solid rgba(50, 34, 20, 0.1);
        }

        .filter-bar .btn {
            padding: 0.5rem 1.2rem;
            font-size: 0.9rem;
            border-radius: 50px;
            transition: all 0.3s ease;
        }

        .filter-bar .btn:not(.btn-outline) {
            background: var(--primary);
            color: #ffffff;
            box-shadow: 0 4px 10px rgba(127, 86, 20, 0.2);
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

        .catalog-head {
            align-items: center;
        }

        .catalog-search {
            display: flex;
            gap: 10px;
            margin: 0 0 18px;
        }

        .catalog-search input {
            border: 1px solid #ddd;
            border-radius: 999px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
            flex: 1;
            font-family: 'Manrope', sans-serif;
            font-size: 1rem;
            outline: none;
            padding: 12px 20px;
        }

        .catalog-filters {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .catalog-filter-toggle {
            align-self: flex-start;
        }

        .advanced-catalog-filters {
            display: none;
            flex-direction: column;
            gap: 15px;
        }

        .advanced-catalog-filters.is-open {
            display: flex;
        }

        .filter-options {
            flex-wrap: wrap;
        }

        .filter-clear {
            border-color: #b6452c;
            color: #b6452c !important;
        }

        .stock-alert {
            background: #b6452c;
            border-radius: 999px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
            color: white;
            font-size: 0.72rem;
            font-weight: 800;
            left: 12px;
            padding: 5px 10px;
            position: absolute;
            top: 52px;
            z-index: 6;
        }

        .detail-stock-alert {
            display: inline-flex;
            margin-bottom: 15px;
            position: static;
        }

        .stock-label,
        .cart-ref,
        .cart-size {
            color: #7f5614;
            display: inline-block;
            font-size: 0.8rem;
            font-weight: 800;
        }

        .sizes-list {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
        }

        .size-tag {
            background: #f0eee9;
            border-radius: 4px;
            color: #333;
            font-size: 0.75rem;
            font-weight: 800;
            padding: 3px 7px;
        }

        .size-tag.active {
            background: var(--primary);
            color: white;
        }

        .size-tag small {
            color: #b6452c;
            margin-left: 3px;
        }

        .price-row {
            align-items: center;
            border-top: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            gap: 12px;
            padding-top: 1rem;
        }

        .price-row .btn {
            padding: 8px 16px;
            font-size: 0.85rem;
        }

        .order-confirmation {
            background: #fdfaf5;
            border: 2px dashed #e2cdb8;
            border-radius: 20px;
            margin-top: 30px;
            padding: 25px;
        }

        .order-confirmation h3 {
            color: var(--primary);
            margin-top: 0;
        }

        .order-summary {
            border-bottom: 1px solid #e2cdb8;
            border-top: 1px solid #e2cdb8;
            display: grid;
            gap: 10px;
            margin: 16px 0;
            padding: 14px 0;
        }

        .order-summary div,
        .order-actions {
            align-items: center;
            display: flex;
            gap: 15px;
            justify-content: space-between;
        }

        .order-actions {
            flex-wrap: wrap;
        }

        .order-actions .btn,
        .order-actions form {
            flex: 1;
            min-width: 190px;
        }

        .order-actions form .btn {
            width: 100%;
        }

        .btn-whatsapp {
            background: #25d366;
            border-color: #25d366;
        }

        /* Toast Notifications */
        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
        }
        .toast {
            background: #2ecc71;
            color: white;
            padding: 12px 24px;
            border-radius: 8px;
            margin-bottom: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            display: flex;
            align-items: center;
            gap: 10px;
            animation: slideIn 0.3s ease-out;
            font-weight: 600;
        }
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }

        /* Mobile Optimization (2 columns) */
        @media (max-width: 768px) {
            .catalog-search {
                flex-direction: column;
            }

            .grid-products {
                grid-template-columns: repeat(2, 1fr) !important;
                gap: 12px !important;
            }
            .product-card {
                border-radius: 12px;
            }
            .grid-products .card-img {
                height: 150px;
            }
            .grid-products .card-content {
                padding: 12px;
            }
            .grid-products .card-content h3 {
                font-size: 0.95rem;
                line-height: 1.2;
            }
            .grid-products .card-content p:not(.ref),
            .grid-products .stock-label {
                display: none;
            }
            .price-row {
                align-items: stretch;
                flex-direction: column;
            }
            .price-row .btn {
                width: 100%;
            }
            .stock-alert {
                font-size: 0.62rem;
                left: 8px;
                padding: 4px 8px;
                top: 46px;
            }
            .order-summary div {
                align-items: flex-start;
                flex-direction: column;
                gap: 4px;
            }
        }

        /* Carousel Globals */
        .carousel-dots {
            position: absolute;
            bottom: 12px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 6px;
            z-index: 5;
        }

        .dot {
            width: 6px;
            height: 6px;
            background: rgba(255,255,255,0.5);
            border-radius: 50%;
            transition: all 0.3s ease;
        }

        .dot.active {
            background: white;
            width: 12px;
            border-radius: 4px;
        }

        .main-img {
            transition: opacity 0.4s ease;
        }
    </style>
    @stack('styles')
</head>

<body data-session-cart='@json(session('carrito', []))' data-cart-sync-url="{{ route('carrito.sincronizar') }}" data-cart-cleared="{{ session('cart_cleared') ? '1' : '0' }}">

    <header>
        <div class="brand">
            <img class="brand-logo" src="{{ asset('images/LOGOLORENTINA.png') }}" alt="Lorentina">

            <div class="brand-text">
                <h1>Lorentina</h1>
                <span>Calzado artesanal con alma de Bucaramanga</span>
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

                @if ($cantidadCarrito > 0)
                    <span class="cart-badge" data-cart-badge>{{ $cantidadCarrito }}</span>
                @else
                    <span class="cart-badge" data-cart-badge hidden>0</span>
                @endif
            </a>

        </nav>
        <div id="toast-container" class="toast-container"></div>
    </header>

    @if (session('success'))
        <div class="container">
            <div class="alert">
                {{ session('success') }}
            </div>
        </div>
    @endif

    @yield('content')

    <footer>
        <p>© 2026 Lorentina – Elegancia y Calidad en Calzado Artesanal</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/heic2any@0.0.4/dist/heic2any.min.js"></script>

    <script>
        let intervals = new Map();

        function startCarousel(card) {
            const images = JSON.parse(card.dataset.images);
            if (images.length <= 1) return;

            const imgElement = card.querySelector('.main-img');
            const dots = card.querySelectorAll('.dot');
            let currentIdx = 0;

            const interval = setInterval(() => {
                currentIdx = (currentIdx + 1) % images.length;
                
                // Fade effect
                imgElement.style.opacity = 0.7;
                setTimeout(() => {
                    const nextSrc = images[currentIdx];
                    setImgSrc(imgElement, nextSrc);
                    imgElement.style.opacity = 1;
                    
                    // Update dots
                    dots.forEach((dot, i) => {
                        dot.classList.toggle('active', i === currentIdx);
                    });
                }, 200);
            }, 2500);

            intervals.set(card, interval);
        }

        function stopCarousel(card) {
            if (intervals.has(card)) {
                clearInterval(intervals.get(card));
                intervals.delete(card);
                
                const images = JSON.parse(card.dataset.images);
                const imgElement = card.querySelector('.main-img');
                const dots = card.querySelectorAll('.dot');
                
                setImgSrc(imgElement, images[0]);
                dots.forEach((dot, i) => {
                    dot.classList.toggle('active', i === 0);
                });
            }
        }

        async function setImgSrc(imgElement, src) {
            if (src.toLowerCase().endsWith('.heic')) {
                // Check if already converted and cached in a data attribute
                if (imgElement.dataset.heicCache && imgElement.dataset.heicOriginal === src) {
                    imgElement.src = imgElement.dataset.heicCache;
                    return;
                }

                try {
                    const response = await fetch(src);
                    const blob = await response.blob();
                    const conversionResult = await heic2any({
                        blob,
                        toType: "image/jpeg",
                        quality: 0.7
                    });
                    
                    const url = URL.createObjectURL(Array.isArray(conversionResult) ? conversionResult[0] : conversionResult);
                    imgElement.src = url;
                    imgElement.dataset.heicCache = url;
                    imgElement.dataset.heicOriginal = src;
                } catch (e) {
                    console.error("Error converting HEIC:", e);
                    imgElement.src = src; // Fallback
                }
            } else {
                imgElement.src = src;
            }
        }

        // Toast Function
        function showToast(message) {
            const container = document.getElementById('toast-container');
            const toast = document.createElement('div');
            toast.className = 'toast';
            toast.innerHTML = `<span>✓</span> ${message}`;
            container.appendChild(toast);
            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transform = 'translateX(100%)';
                toast.style.transition = 'all 0.3s ease';
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }

        // Initialize HEIC images on load
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('img').forEach(img => {
                if (img.src.toLowerCase().endsWith('.heic')) {
                    setImgSrc(img, img.src);
                }
            });

            // Check for success message in session and show toast
            @if(session('success'))
                showToast("{{ session('success') }}");
            @endif
        });
    </script>
    <script>
        const CART_STORAGE_KEY = 'lorentina_cart';

        function showToast(message) {
            const container = document.getElementById('toast-container');
            if (!container) return;

            const toast = document.createElement('div');
            toast.className = 'toast';
            toast.innerHTML = `<span>✓</span> ${message}`;
            container.appendChild(toast);
            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transform = 'translateX(100%)';
                toast.style.transition = 'all 0.3s ease';
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }

        function getStoredCart() {
            try {
                return JSON.parse(localStorage.getItem(CART_STORAGE_KEY) || '{}');
            } catch (error) {
                return {};
            }
        }

        function setStoredCart(cart) {
            localStorage.setItem(CART_STORAGE_KEY, JSON.stringify(cart || {}));
            updateCartBadge(cart || {});
        }

        function updateCartBadge(cart) {
            const badge = document.querySelector('[data-cart-badge]');
            if (!badge) return;

            const cantidad = Object.values(cart || {}).reduce((total, item) => total + Number(item.cantidad || 0), 0);
            badge.textContent = cantidad;
            badge.hidden = cantidad < 1;
        }

        function buildLorentinaOrderMessage(items) {
            const values = Object.values(items || {});
            let total = 0;
            const lines = values.map(item => {
                const subtotal = Number(item.precio || 0) * Number(item.cantidad || 0);
                total += subtotal;
                const ref = item.referencia || item.nombre || 'Producto';
                const color = item.color ? ` ${item.color}` : '';
                const pares = Number(item.cantidad || 0) === 1 ? 'par' : 'pares';

                return `• ${ref}${color} - T.${item.talla}: ${item.cantidad} ${pares}`;
            });

            return [
                '🛍️ *Pedido Lorentina*',
                '─────────────────',
                ...lines,
                '─────────────────',
                `Total: $${total.toLocaleString('es-CO')}`
            ].join('\n');
        }

        async function syncStoredCartToSession(cart) {
            const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
            const syncUrl = document.body.dataset.cartSyncUrl;

            if (!csrf || !syncUrl || Object.keys(cart || {}).length === 0) return;

            try {
                await fetch(syncUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ carrito: cart })
                });
            } catch (error) {
                console.warn('No se pudo sincronizar el carrito local.', error);
            }
        }

        function setupLiveCatalogSearch() {
            const input = document.querySelector('[data-live-search]');
            if (!input) return;

            const form = input.closest('form');
            let timer = null;

            input.addEventListener('input', () => {
                clearTimeout(timer);
                timer = setTimeout(() => form.submit(), 450);
            });
        }

        function setupCatalogFilterToggle() {
            const toggle = document.querySelector('[data-filter-toggle]');
            const panel = document.querySelector('[data-filter-panel]');
            if (!toggle || !panel) return;

            toggle.addEventListener('click', () => {
                const isOpen = panel.classList.toggle('is-open');
                toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
                toggle.textContent = isOpen ? 'Ocultar filtros' : 'Filtrar más';
            });
        }

        document.addEventListener('DOMContentLoaded', () => {
            if (document.body.dataset.cartCleared === '1') {
                setStoredCart({});
                setupLiveCatalogSearch();
                return setupCatalogFilterToggle();
            }

            const sessionCart = JSON.parse(document.body.dataset.sessionCart || '{}');
            const storedCart = getStoredCart();

            if (Object.keys(sessionCart).length > 0) {
                setStoredCart(sessionCart);
            } else if (Object.keys(storedCart).length > 0) {
                updateCartBadge(storedCart);

                if (window.location.pathname === '{{ parse_url(route('carrito.ver'), PHP_URL_PATH) }}') {
                    syncStoredCartToSession(storedCart).then(() => window.location.reload());
                } else {
                    syncStoredCartToSession(storedCart);
                }
            } else {
                updateCartBadge({});
            }

            setupLiveCatalogSearch();
            setupCatalogFilterToggle();
        });
    </script>
    @stack('scripts')
</body>
</html>
