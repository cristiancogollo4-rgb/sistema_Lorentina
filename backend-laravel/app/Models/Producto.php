<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    public $timestamps = false;

    protected $table = 'productos';

    protected $fillable = [
        'nombre_modelo',
        'descripcion',
        'referencia',
        'color',
        'tipo',
        'precio_detal',
        'precio_mayor',
        'costo_produccion',
        'activo',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'referencia' => 'string',
            'color' => 'string',
            'tipo' => 'string',
            'precio_detal' => 'float',
            'precio_mayor' => 'float',
            'costo_produccion' => 'float',
            'activo' => 'boolean',
            'created_at' => 'datetime',
        ];
    }
}
