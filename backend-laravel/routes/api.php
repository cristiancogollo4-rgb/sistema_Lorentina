<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductionController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\TarifaController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\VentaController;
use App\Http\Controllers\EcommerceAdminController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);
Route::get('/usuarios', [UserController::class, 'index']);
Route::post('/usuarios', [UserController::class, 'store']);
Route::get('/usuarios/{id}', [UserController::class, 'show']);
Route::put('/usuarios/{id}', [UserController::class, 'update']);
Route::delete('/usuarios/{id}', [UserController::class, 'destroy']);
Route::post('/stock/masivo', [StockController::class, 'masivo']);
Route::get('/stock/zapatos', [StockController::class, 'index']);
Route::post('/stock/transferir', [StockController::class, 'transferir']);
Route::get('/empleados/corte', [ProductionController::class, 'empleadosCorte']);
Route::get('/tarifas', [TarifaController::class, 'index']);
Route::post('/tarifas', [TarifaController::class, 'store']);
Route::post('/tarifas/actualizar', [TarifaController::class, 'actualizar']);
Route::get('/produccion/catalogo', [ProductionController::class, 'catalogoProduccion']);
Route::post('/produccion/productos', [ProductionController::class, 'crearProductoProduccion']);
Route::post('/produccion', [ProductionController::class, 'store']);
Route::get('/mis-tareas/{empleadoId}', [ProductionController::class, 'misTareas']);
Route::get('/nomina/resumen', [ProductionController::class, 'nominaResumen']);
Route::post('/nomina/pagos', [ProductionController::class, 'registrarPagoNomina']);
Route::get('/nomina/{empleadoId}', [ProductionController::class, 'nomina']);
Route::get('/produccion/tablero', [ProductionController::class, 'tablero']);
Route::post('/produccion/asignar', [ProductionController::class, 'asignar']);
Route::post('/produccion/terminar-tarea', [ProductionController::class, 'terminarTarea']);
Route::post('/produccion/pasar-a-stock', [ProductionController::class, 'pasarAStock']);

// Clientes
Route::get('/clientes', [ClienteController::class, 'index']);
Route::post('/clientes', [ClienteController::class, 'store']);
Route::get('/clientes/{id}', [ClienteController::class, 'show']);
Route::put('/clientes/{id}', [ClienteController::class, 'update']);

// Ventas
Route::get('/ventas', [VentaController::class, 'index']);
Route::get('/ventas/catalogo', [VentaController::class, 'catalogo']);
Route::post('/ventas', [VentaController::class, 'store']);

// E-commerce admin
Route::get('/ecommerce/productos', [EcommerceAdminController::class, 'productos']);
Route::patch('/ecommerce/productos/{producto}/visibilidad', [EcommerceAdminController::class, 'actualizarVisibilidad']);
Route::patch('/ecommerce/productos/{producto}/precio', [EcommerceAdminController::class, 'actualizarPrecio']);
Route::patch('/ecommerce/productos/{producto}/promocion', [EcommerceAdminController::class, 'actualizarPromocion']);
