<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Local extends Model
{
    public $timestamps = false;

    protected $table = 'locales';

    protected $fillable = [
        'nombre',
        'direccion',
        'activo',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
            'created_at' => 'datetime',
        ];
    }
}
