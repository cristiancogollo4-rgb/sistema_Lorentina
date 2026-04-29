<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventarioMovimiento extends Model
{
    protected $table = 'inventario_movimientos';

    public $timestamps = false;

    protected $fillable = [
        'tipo_movimiento',
        'orden_produccion_id',
        'venta_id',
        'referencia',
        'color',
        'tipo',
        'sucursal',
        'talla',
        'cantidad',
        'usuario_id',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'orden_produccion_id' => 'integer',
            'venta_id' => 'integer',
            'talla' => 'integer',
            'cantidad' => 'integer',
            'usuario_id' => 'integer',
            'created_at' => 'datetime',
        ];
    }
}
