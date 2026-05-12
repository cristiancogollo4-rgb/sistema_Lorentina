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
        'imagenes',
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
            'imagenes' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function stocks()
    {
        return $this->hasMany(Stock::class, 'producto_id');
    }

    public function getTallasDisponiblesAttribute()
    {
        $inventario = \DB::table('inventario_zapatos')
            ->where('referencia', $this->referencia)
            ->where('color', $this->color)
            ->first();

        if (!$inventario) return [];

        $tallas = [];
        foreach (range(35, 42) as $t) {
            $col = "t$t";
            if (($inventario->$col ?? 0) > 0) {
                $tallas[] = $t;
            }
        }

        return $tallas;
    }

    public function getImagenSrcAttribute(): string
    {
        return $this->todas_las_imagenes_src[0] ?? asset('images/default-shoe.jpg');
    }

    public function getTodasLasImagenesSrcAttribute(): array
    {
        $urls = [];
        $imagenes = $this->imagenes ?? [];

        if (empty($imagenes) && $this->imagen) {
            $imagenes = [$this->imagen];
        }

        foreach ($imagenes as $img) {
            if (str_contains($img, 'drive.google.com')) {
                if (preg_match('/(?:id=|\/d\/)([a-zA-Z0-9_-]+)/', $img, $matches)) {
                    $driveId = $matches[1];
                    $urls[] = "https://drive.google.com/thumbnail?id={$driveId}&sz=w600";
                }
            } else {
                $urls[] = str_starts_with($img, 'http')
                    ? $img
                    : asset('images/' . $img);
            }
        }

        if (empty($urls)) {
            $urls[] = asset('images/default-shoe.jpg');
        }

        return $urls;
    }
}
