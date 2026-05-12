<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Venta extends Model
{
    public $timestamps = false;

    protected $table = 'ventas';

    protected $fillable = [
        'cliente_id',
        'vendedor_id',
        'canal_venta',
        'local_id',
        'fecha_venta',
        'total',
        'metodo_pago',
        'titular_cuenta',
        'notas',
    ];

    protected function casts(): array
    {
        return [
            'cliente_id' => 'integer',
            'vendedor_id' => 'integer',
            'local_id' => 'integer',
            'fecha_venta' => 'datetime',
            'total' => 'float',
        ];
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function vendedor()
    {
        return $this->belongsTo(User::class, 'vendedor_id');
    }

    public function local()
    {
        return $this->belongsTo(Local::class, 'local_id');
    }

    public function items()
    {
        return $this->hasMany(DetalleVenta::class, 'venta_id');
    }
}
