<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    protected $table = 'stocks';
    public $timestamps = false;

    protected $fillable = [
        'producto_id',
        'talla',
        'local_id',
        'pares_disponibles',
        'updated_at'
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }
}
