<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClienteEcommerceDireccion extends Model
{
    protected $table = 'cliente_ecommerce_direcciones';

    protected $fillable = [
        'cliente_ecommerce_id',
        'alias',
        'departamento',
        'municipio',
        'direccion',
        'detalle',
        'principal',
    ];

    protected function casts(): array
    {
        return [
            'principal' => 'boolean',
        ];
    }

    public function cliente()
    {
        return $this->belongsTo(ClienteEcommerce::class, 'cliente_ecommerce_id');
    }
}
