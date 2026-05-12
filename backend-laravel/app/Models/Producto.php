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
        return array_keys($this->stock_por_talla);
    }

    public function getStockPorTallaAttribute(): array
    {
        $inventario = $this->getInventario();

        if (!$inventario) return [];

        $stock = [];
        foreach (range(35, 42) as $t) {
            $col = "t$t";
            $cantidad = (int) ($inventario->$col ?? 0);
            if ($cantidad > 0) {
                $stock[$t] = $cantidad;
            }
        }

        return $stock;
    }

    public function getTieneStockBajoAttribute(): bool
    {
        foreach ($this->stock_por_talla as $cantidad) {
            if ($cantidad > 0 && $cantidad < 3) {
                return true;
            }
        }

        return false;
    }

    public function getTotalStockAttribute()
    {
        $inventario = $this->getInventario();
        if (!$inventario) return 0;

        $total = 0;
        foreach (range(35, 42) as $t) {
            $col = "t$t";
            $total += (int)($inventario->$col ?? 0);
        }
        return $total;
    }

    private function getInventario()
    {
        return \DB::table('inventario_zapatos')
            ->where('referencia', $this->referencia)
            ->where('color', $this->color)
            ->first();
    }

    public function scopeSearch($query, $term)
    {
        if (!$term) return $query;
        return $query->where(function($q) use ($term) {
            $q->where('productos.nombre_modelo', 'like', "%{$term}%")
              ->orWhere('productos.referencia', 'like', "%{$term}%")
              ->orWhere('productos.color', 'like', "%{$term}%");
        });
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
                $img = $this->preferWebpForHeic($img);
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

    private function preferWebpForHeic(string $path): string
    {
        if (! str_ends_with(strtolower($path), '.heic') || str_starts_with($path, 'http')) {
            return $path;
        }

        $webpPath = preg_replace('/\.heic$/i', '.webp', $path);
        if (! $webpPath) {
            return $path;
        }

        return file_exists(public_path('images/' . $webpPath)) ? $webpPath : $path;
    }
}
