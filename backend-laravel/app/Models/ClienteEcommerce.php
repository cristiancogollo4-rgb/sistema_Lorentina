<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClienteEcommerce extends Model
{
    protected $table = 'clientes_ecommerce';

    protected $fillable = [
        'nombre',
        'telefono',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
    ];

    public function pedidos()
    {
        return $this->hasMany(PedidoEcommerce::class, 'cliente_ecommerce_id');
    }

    public function direcciones()
    {
        return $this->hasMany(ClienteEcommerceDireccion::class, 'cliente_ecommerce_id');
    }
}
