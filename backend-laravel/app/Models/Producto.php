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

            // Detect Google Drive thumbnail URLs and transform them for better embedding
            if (str_contains($imagen, 'drive.google.com') && str_contains($imagen, 'id=')) {
                // Extract ID using regex
                if (preg_match('/id=([a-zA-Z0-9_-]+)/', $imagen, $matches)) {
                    $driveId = $matches[1];
                    // Return a more stable and direct embed URL
                    return "https://lh3.googleusercontent.com/d/{$driveId}";
                }
            }

            return str_starts_with($imagen, 'http')
                ? $imagen
                : asset('images/' . $imagen);
        }

        return asset('images/default-shoe.jpg');
    }
}
