<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PedidoEcommerceItem extends Model
{
    protected $table = 'pedido_ecommerce_items';

    public $timestamps = false;

    protected $fillable = [
        'pedido_ecommerce_id',
        'producto_id',
        'nombre',
        'referencia',
        'color',
        'tipo',
        'talla',
        'cantidad',
        'precio_unitario',
        'subtotal',
    ];

    protected function casts(): array
    {
        return [
            'pedido_ecommerce_id' => 'integer',
            'producto_id' => 'integer',
            'talla' => 'integer',
            'cantidad' => 'integer',
            'precio_unitario' => 'float',
            'subtotal' => 'float',
        ];
    }

    public function pedido()
    {
        return $this->belongsTo(PedidoEcommerce::class, 'pedido_ecommerce_id');
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }
}
