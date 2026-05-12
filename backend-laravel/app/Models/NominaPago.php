<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NominaPago extends Model
{
    public $timestamps = false;

    protected $table = 'nomina_pagos';

    protected $fillable = [
        'empleado_id',
        'periodo_inicio',
        'periodo_fin',
        'fecha_pago',
        'estado',
        'total_pares',
        'total_tareas',
        'total_pagado',
        'detalle',
        'notas',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'empleado_id' => 'integer',
            'periodo_inicio' => 'date',
            'periodo_fin' => 'date',
            'fecha_pago' => 'date',
            'total_pares' => 'integer',
            'total_tareas' => 'integer',
            'total_pagado' => 'integer',
            'detalle' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function empleado()
    {
        return $this->belongsTo(User::class, 'empleado_id');
    }
}
