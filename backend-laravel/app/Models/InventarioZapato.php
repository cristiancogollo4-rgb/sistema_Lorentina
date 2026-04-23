<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventarioZapato extends Model
{
    protected $table = 'inventario_zapatos';

    public $timestamps = false;

    protected $fillable = [
        'referencia',
        'color',
        'sucursal',
        'tipo',
        't35',
        't36',
        't37',
        't38',
        't39',
        't40',
        't41',
        't42',
        'total',
        'updated_at',
    ];

    protected function casts(): array
    {
        return [
            't35' => 'integer',
            't36' => 'integer',
            't37' => 'integer',
            't38' => 'integer',
            't39' => 'integer',
            't40' => 'integer',
            't41' => 'integer',
            't42' => 'integer',
            'total' => 'integer',
            'updated_at' => 'datetime',
        ];
    }
}
