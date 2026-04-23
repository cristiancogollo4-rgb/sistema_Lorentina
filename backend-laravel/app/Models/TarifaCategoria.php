<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TarifaCategoria extends Model
{
    protected $table = 'tarifa_categorias';

    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'precio_corte',
        'precio_armado',
        'precio_costura',
        'precio_soladura',
        'precio_emplantillado',
    ];

    protected function casts(): array
    {
        return [
            'precio_corte' => 'integer',
            'precio_armado' => 'integer',
            'precio_costura' => 'integer',
            'precio_soladura' => 'integer',
            'precio_emplantillado' => 'integer',
        ];
    }
}
