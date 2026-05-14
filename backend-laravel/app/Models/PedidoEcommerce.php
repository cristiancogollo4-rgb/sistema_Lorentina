<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PedidoEcommerce extends Model
{
    protected $table = 'pedidos_ecommerce';

    public $timestamps = false;

    protected $fillable = [
        'cliente_ecommerce_id',
        'codigo',
        'estado',
        'estado_pago',
        'metodo_pago',
        'cliente_nombre',
        'cliente_telefono',
        'cliente_email',
        'cliente_ciudad',
        'cliente_direccion',
        'notas',
        'subtotal',
        'envio',
        'total',
        'created_at',
        'updated_at',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'float',
            'envio' => 'float',
            'total' => 'float',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function items()
    {
        return $this->hasMany(PedidoEcommerceItem::class, 'pedido_ecommerce_id');
    }

    public function cliente()
    {
        return $this->belongsTo(ClienteEcommerce::class, 'cliente_ecommerce_id');
    }
}
