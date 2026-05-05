<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EcommerceController;

Route::get('/', [EcommerceController::class, 'landing'])->name('landing');

Route::get('/productos', [EcommerceController::class, 'productos'])->name('productos.index');

Route::post('/carrito/agregar/{id}', [EcommerceController::class, 'agregarCarrito'])->name('carrito.agregar');

Route::get('/carrito', [EcommerceController::class, 'verCarrito'])->name('carrito.ver');

Route::post('/carrito/eliminar/{id}', [EcommerceController::class, 'eliminarCarrito'])->name('carrito.eliminar');

Route::post('/carrito/vaciar', [EcommerceController::class, 'vaciarCarrito'])->name('carrito.vaciar');