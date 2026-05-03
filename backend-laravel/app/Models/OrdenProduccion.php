<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrdenProduccion extends Model
{
    protected $table = 'ordenes_produccion';

    public $timestamps = false;

    protected $fillable = [
        'numero_orden',
        'fecha_inicio',
        'fecha_fin_corte',
        'fecha_fin_armado',
        'fecha_fin_costura',
        'fecha_fin_soladura',
        'fecha_fin_emplantillado',
        'fecha_fin_terminado',
        'estado',
        'referencia',
        'color',
        'foto_url',
        'materiales',
        'observacion',
        'categoria',
        'precio_corte',
        'precio_armado',
        'precio_costura',
        'precio_soladura',
        'precio_emplantillado',
        'destino',
        'cliente_id',
        'cortador_id',
        'armador_id',
        'costurero_id',
        'solador_id',
        'emplantillador_id',
        't34',
        't35',
        't36',
        't37',
        't38',
        't39',
        't40',
        't41',
        't42',
        't43',
        't44',
        'total_pares',
    ];

    protected function casts(): array
    {
        return [
            'fecha_inicio' => 'datetime',
            'fecha_fin_corte' => 'datetime',
            'fecha_fin_armado' => 'datetime',
            'fecha_fin_costura' => 'datetime',
            'fecha_fin_soladura' => 'datetime',
            'fecha_fin_emplantillado' => 'datetime',
            'fecha_fin_terminado' => 'datetime',
            'precio_corte' => 'integer',
            'precio_armado' => 'integer',
            'precio_costura' => 'integer',
            'precio_soladura' => 'integer',
            'precio_emplantillado' => 'integer',
            'cliente_id' => 'integer',
            'cortador_id' => 'integer',
            'armador_id' => 'integer',
            'costurero_id' => 'integer',
            'solador_id' => 'integer',
            'emplantillador_id' => 'integer',
            't34' => 'integer',
            't35' => 'integer',
            't36' => 'integer',
            't37' => 'integer',
            't38' => 'integer',
            't39' => 'integer',
            't40' => 'integer',
            't41' => 'integer',
            't42' => 'integer',
            't43' => 'integer',
            't44' => 'integer',
            'total_pares' => 'integer',
        ];
    }

    public function cliente()
    {
        return $this->belongsTo(\App\Models\Cliente::class, 'cliente_id');
    }
}
