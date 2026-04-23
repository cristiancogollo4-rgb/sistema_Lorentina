<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductionController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\TarifaController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);
Route::get('/usuarios', [UserController::class, 'index']);
Route::post('/usuarios', [UserController::class, 'store']);
Route::get('/usuarios/{id}', [UserController::class, 'show']);
Route::put('/usuarios/{id}', [UserController::class, 'update']);
Route::delete('/usuarios/{id}', [UserController::class, 'destroy']);
Route::post('/stock/masivo', [StockController::class, 'masivo']);
Route::get('/stock/zapatos', [StockController::class, 'index']);
Route::get('/empleados/corte', [ProductionController::class, 'empleadosCorte']);
Route::get('/tarifas', [TarifaController::class, 'index']);
Route::post('/tarifas/actualizar', [TarifaController::class, 'actualizar']);
Route::post('/produccion', [ProductionController::class, 'store']);
Route::get('/mis-tareas/{empleadoId}', [ProductionController::class, 'misTareas']);
Route::get('/nomina/{empleadoId}', [ProductionController::class, 'nomina']);
Route::get('/produccion/tablero', [ProductionController::class, 'tablero']);
Route::post('/produccion/asignar', [ProductionController::class, 'asignar']);
Route::post('/produccion/terminar-tarea', [ProductionController::class, 'terminarTarea']);
