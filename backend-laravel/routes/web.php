<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EcommerceController;
use App\Http\Controllers\AdminProductoController;

Route::get('/', [EcommerceController::class, 'landing'])->name('landing');

Route::get('/productos', [EcommerceController::class, 'productos'])->name('productos.index');

Route::get('/productos/{id}', [EcommerceController::class, 'show'])->name('productos.show');

Route::post('/carrito/agregar/{id}', [EcommerceController::class, 'agregarCarrito'])->name('carrito.agregar');

Route::get('/carrito', [EcommerceController::class, 'verCarrito'])->name('carrito.ver');

Route::post('/carrito/actualizar/{id}', [EcommerceController::class, 'actualizarCarrito'])->name('carrito.actualizar');

Route::post('/carrito/eliminar/{id}', [EcommerceController::class, 'eliminarCarrito'])->name('carrito.eliminar');

Route::post('/carrito/vaciar', [EcommerceController::class, 'vaciarCarrito'])->name('carrito.vaciar');

Route::get('/admin/productos', [AdminProductoController::class, 'index'])->name('admin.productos.index');

Route::get('/admin/productos/crear', [AdminProductoController::class, 'crear'])->name('admin.productos.crear');

Route::post('/admin/productos/guardar', [AdminProductoController::class, 'guardar'])->name('admin.productos.guardar');

Route::post('/admin/productos/{id}/estado', [AdminProductoController::class, 'cambiarEstado'])->name('admin.productos.estado');