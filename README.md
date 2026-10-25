<div align="center">

<img src="https://capsule-render.vercel.app/api?type=soft&color=gradient&customColorList=22,21,29&height=150&section=header&text=LoreTina%20ERP&fontSize=48&fontColor=fff&animation=twinkle-frequency&desc=Sistema%20integral%20de%20gesti%C3%B3n%20para%20f%C3%A1brica%20de%20calzado&descSize=16" width="100%" />

**Monorepo full-stack: API Laravel · Dashboard React · App móvil Android · E-commerce público**

[![PHP](https://img.shields.io/badge/PHP-%5E8.3-777BB4?style=flat-square&logo=php&logoColor=white)](https://www.php.net/)
[![Laravel](https://img.shields.io/badge/Laravel-13-FF2D20?style=flat-square&logo=laravel&logoColor=white)](https://laravel.com/)
[![React](https://img.shields.io/badge/React-19-61DAFB?style=flat-square&logo=react&logoColor=white)](https://react.dev/)
[![Kotlin](https://img.shields.io/badge/Kotlin-2.0-7F52FF?style=flat-square&logo=kotlin&logoColor=white)](https://kotlinlang.org/)
[![Vite](https://img.shields.io/badge/Vite-7-646CFF?style=flat-square&logo=vite&logoColor=white)](https://vite.dev/)
[![SQLite](https://img.shields.io/badge/SQLite%20%7C%20MySQL-003B57?style=flat-square&logo=sqlite&logoColor=white)](https://www.sqlite.org/)

</div>

---

## 📌 ¿Qué es?

**LoreTina** es el sistema operativo de una **fábrica de calzado femenino** (Bucaramanga, Colombia 🇨🇴): controla el ciclo completo del negocio — producción por etapas, inventario entre sucursales, ventas internas y por canales digitales, e-commerce con pagos, clientes B2B y **nómina semanal** de trabajadores.

A diferencia de un CRUD tradicional, integra **tres clientes para el mismo backend**:

| Cliente | Rol en el negocio | Tech |
| --- | --- | --- |
| 🖥️ Dashboard web | Administradores y vendedores: control total de la fábrica | React 19 + axios |
| 📱 App Android | Operarios de fábrica: reciben tareas y consultan su nómina | Kotlin + Compose |
| 🛒 E-commerce público | Compradores finales: catálogo, carrito y checkout | Laravel Blade + Tailwind |

---

## ⚙️ Módulos del sistema

### 🏭 Producción y trazabilidad
- Órdenes de fabricación por referencia, modelo y talla (34–44)
- **Tablero de trazabilidad** por etapas: `CORTE → ARMADO → COSTURA → SOLADURA → EMPLANTILLADO`
- Asignación de operarios por etapa y cierre validado por el propio trabajador
- Panel de tareas individuales en la app móvil (`/mis-tareas/{operario}`)

### 💰 Nómina semanal
- Dos modalidades calculadas por semana (pago el sábado):
  - **Por producción:** pares terminados × tarifa de cada etapa
  - **Por ventas:** comisión por venta detal ($5.000) / mayorista ($2.500 COP)
- Resumen global de pagados vs. pendientes e historial de pagos por empleado

### 📦 Inventario multi-sucursal
- Stock por referencia × color × talla en sucursales `CABECERA` y `FABRICA`
- Movimientos auditables: `IN`, `OUT`, `TRANSFER`, `AJUSTE`
- **Transferencias entre sucursales** y carga masiva (solo admin)
- Descuento de inventario atómico con bloqueo concurrente (`lockForUpdate`)

### 🛒 Ventas multicanal
- Canales: `ONLINE`, `LOCAL`, `WHATSAPP`, `INSTAGRAM`, `MESSENGER`
- Vista unificada de ventas internas + pedidos del e-commerce
- Precios detal / mayor junto con reglas de catálogo para mayoristas
- KPIs del período en el dashboard

### 🤝 Clientes B2B y usuarios
- CRUD de clientes mayoristas con vendedor asignado y ubicación (Colombia: departamento/ciudad)
- Gestión de recursos humanos: usuarios con rol, estado y datos personales

### 🌐 E-commerce público
- Landing, catálogo con galería de producto, carrito por sesión y checkout
- Registro/login de clientes con libreta de direcciones
- **Mercado Pago** integrado (configurable por `.env`) con fallback de pedido por **WhatsApp**
- Panel admin de promociones, precios online y visibilidad de catálogo

---

## 🪪 Roles

| Rol | Accesos |
| --- | --- |
| **ADMIN** | Módulos completos: clientes, empleados, stock (carga masiva y transferencias), producción, ventas, e-commerce, nómina |
| **VENDEDOR** | Sus clientes, ventas, stock (solo lectura), comisiones |
| **Operario** (CORTE, ARMADOR, COSTURERO, SOLADOR, EMPLANTILLADOR) | App móvil: sus tareas y su nómina. No accede al panel web |

---

## 🏗️ Arquitectura

```
                    ┌─ frontend/  (React 19 · Vite · axios)      ── dashboard interno
  backend-laravel/ ─┼─ Movil/ApplorentinaPG (Kotlin Compose)      ── app de operarios
  (API REST :8000) ─┼─ resources/views (Blade + Tailwind)         ── e-commerce público
                    └─ SQLite (dev) / MySQL (prod) · Supabase sync
```

## 🚀 Puesta en marcha

**Requisitos:** PHP ≥ 8.3 · Composer · Node ≥ 20 · Android Studio

```bash
# 1) Backend — API en http://localhost:8000
cd backend-laravel
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve

# 2) Dashboard React
cd ../frontend
npm install
npm run dev

# 3) App Android
# Abrir Movil/ApplorentinaPG en Android Studio,
# detecta automáticamente la IP LAN del servidor (puerto 8000)
```

<details>
<summary>🗄️ Configurar MySQL en lugar de SQLite</summary>

En `backend-laravel/.env`, descomenta:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=root
DB_PASSWORD=
```
</details>

<details>
<summary>🔐 Credenciales de demo (seeders)</summary>

Todos los usuarios sembrados usan la contraseña `1234`:

| Usuario | Rol |
| --- | --- |
| `cristian` | ADMIN |
| `juan.vendedor`, `silvia.vendedor` | VENDEDOR |
| +10 operarios de fábrica | App móvil |
</details>

---

## 📄 Estructura del repo

```
sistema_Lorentina/
├── backend-laravel/     # API Laravel 13 + e-commerce Blade
│   └── app/Http/Controllers
├── frontend/            # Dashboard de gestión (React 19 + Vite)
├── Movil/ApplorentinaPG # App Android de operarios (Kotlin + Compose)
├── docs/                # Documentación de negocio y producto
└── scratch/             # Scripts de mantenimiento (sync de catálogo)
```

---

## 🗺️ Roadmap

- [ ] Módulo de apartados para vendedores *(en diseño)* 🔜
- [ ] Trazabilidad por lotes de producto (ver `docs/recomendacion-identidad-producto.md`)
- [ ] Pagos Mercado Pago activos por defecto
- [ ] Notificaciones push de tareas en la app móvil

<div align="center">
<img src="https://capsule-render.vercel.app/api?type=soft&color=gradient&customColorList=22,21,29&height=90&section=footer" width="100%" />
</div>
