<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetalleVenta extends Model
{
    public $timestamps = false;

    protected $table = 'detalle_ventas';

    protected $fillable = [
        'venta_id',
        'producto_id',
        'orden_produccion_id',
        'numero_orden',
        'referencia',
        'color',
        'talla',
        'cantidad',
        'precio_unitario',
    ];

    protected function casts(): array
    {
        return [
            'venta_id' => 'integer',
            'producto_id' => 'integer',
            'orden_produccion_id' => 'integer',
            'talla' => 'integer',
            'cantidad' => 'integer',
            'precio_unitario' => 'float',
        ];
    }

    public function venta()
    {
        return $this->belongsTo(Venta::class, 'venta_id');
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    public function ordenProduccion()
    {
        return $this->belongsTo(OrdenProduccion::class, 'orden_produccion_id');
    }
}
