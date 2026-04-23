<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'telefono',
        'email',
        'direccion',
        'pais',
        'departamento',
        'region_estado',
        'ciudad',
        'codigo_postal',
        'moneda_preferida',
        'tipo_cliente',   // MAYORISTA | DETAL
        'activo',
        'vendedor_id',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function vendedor()
    {
        return $this->belongsTo(User::class, 'vendedor_id');
    }
}
