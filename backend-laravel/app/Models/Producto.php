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
        'imagen',
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
            'imagen' => 'string',
            'created_at' => 'datetime',
        ];
    }

    public function getImagenSrcAttribute(): string
    {
        if ($this->imagen) {
            $imagen = (string) $this->imagen;

            // Use optimized thumbnail format to avoid rate limits (429 errors)
            if (str_contains($imagen, 'drive.google.com') && str_contains($imagen, 'id=')) {
                if (preg_match('/id=([a-zA-Z0-9_-]+)/', $imagen, $matches)) {
                    $driveId = $matches[1];
                    // sz=w600 is enough for a good display while keeping requests lightweight
                    return "https://drive.google.com/thumbnail?id={$driveId}&sz=w600";
                }
            }

            if (str_contains($imagen, 'drive.google.com') && str_contains($imagen, '/d/')) {
                if (preg_match('/\/d\/([a-zA-Z0-9_-]+)/', $imagen, $matches)) {
                    $driveId = $matches[1];
                    return "https://drive.google.com/thumbnail?id={$driveId}&sz=w600";
                }
            }

            return str_starts_with($imagen, 'http')
                ? $imagen
                : asset('images/' . $imagen);
        }

        return asset('images/default-shoe.jpg');
    }
}
