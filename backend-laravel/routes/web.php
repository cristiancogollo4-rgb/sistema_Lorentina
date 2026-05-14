<?php

use App\Http\Controllers\AdminProductoController;
use App\Http\Controllers\EcommerceController;
use Illuminate\Support\Facades\Route;

Route::get('/', [EcommerceController::class, 'landing'])->name('landing');

Route::get('/productos', [EcommerceController::class, 'productos'])->name('productos.index');

Route::get('/productos/{id}', [EcommerceController::class, 'show'])->name('productos.show');

Route::post('/carrito/agregar/{id}', [EcommerceController::class, 'agregarCarrito'])->name('carrito.agregar');

Route::get('/carrito', [EcommerceController::class, 'verCarrito'])->name('carrito.ver');

Route::post('/carrito/actualizar/{id}', [EcommerceController::class, 'actualizarCarrito'])->name('carrito.actualizar');

Route::post('/carrito/eliminar/{id}', [EcommerceController::class, 'eliminarCarrito'])->name('carrito.eliminar');

Route::post('/carrito/vaciar', [EcommerceController::class, 'vaciarCarrito'])->name('carrito.vaciar');

Route::post('/carrito/sincronizar', [EcommerceController::class, 'sincronizarCarrito'])->name('carrito.sincronizar');

Route::get('/checkout', [EcommerceController::class, 'checkout'])->name('checkout.index');

Route::post('/checkout', [EcommerceController::class, 'guardarCheckout'])->name('checkout.guardar');

Route::get('/checkout/gracias/{pedido}', [EcommerceController::class, 'gracias'])->name('checkout.gracias');

Route::get('/clientes/login', [EcommerceController::class, 'loginClienteForm'])->name('cliente.login');

Route::post('/clientes/login', [EcommerceController::class, 'loginCliente'])->name('cliente.login.guardar');

Route::get('/clientes/registro', [EcommerceController::class, 'registrarClienteForm'])->name('cliente.registro');

Route::post('/clientes/registro', [EcommerceController::class, 'registrarCliente'])->name('cliente.registro.guardar');

Route::get('/mi-cuenta', [EcommerceController::class, 'cuentaCliente'])->name('cliente.cuenta');

Route::post('/mi-cuenta/direcciones', [EcommerceController::class, 'guardarDireccionCliente'])->name('cliente.direcciones.guardar');

Route::post('/mi-cuenta/direcciones/{direccion}', [EcommerceController::class, 'actualizarDireccionCliente'])->name('cliente.direcciones.actualizar');

Route::post('/clientes/logout', [EcommerceController::class, 'logoutCliente'])->name('cliente.logout');

Route::get('/admin/productos', [AdminProductoController::class, 'index'])->name('admin.productos.index');

Route::get('/admin/productos/crear', [AdminProductoController::class, 'crear'])->name('admin.productos.crear');

Route::post('/admin/productos/guardar', [AdminProductoController::class, 'guardar'])->name('admin.productos.guardar');

Route::post('/admin/productos/{id}/estado', [AdminProductoController::class, 'cambiarEstado'])->name('admin.productos.estado');

Route::get('/sitemap.xml', [EcommerceController::class, 'sitemap'])->name('sitemap');
