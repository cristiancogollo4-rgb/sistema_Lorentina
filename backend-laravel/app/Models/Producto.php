<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Producto extends Model
{
    public $timestamps = false;

    protected $table = 'productos';

    /** @var array<string, array<int, string>>|null */
    private static ?array $catalogImageIndex = null;

    protected $fillable = [
        'nombre_modelo',
        'descripcion',
        'referencia',
        'color',
        'tipo',
        'tarifa_categoria_id',
        'precio_detal',
        'precio_mayor',
        'costo_produccion',
        'activo',
        'visible_ecommerce',
        'en_promocion',
        'precio_promocion',
        'etiqueta_promocion',
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
            'tarifa_categoria_id' => 'integer',
            'precio_detal' => 'float',
            'precio_mayor' => 'float',
            'costo_produccion' => 'float',
            'activo' => 'boolean',
            'visible_ecommerce' => 'boolean',
            'en_promocion' => 'boolean',
            'precio_promocion' => 'float',
            'imagen' => 'string',
            'imagenes' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function stocks()
    {
        return $this->hasMany(Stock::class, 'producto_id');
    }

    public function tarifaCategoria()
    {
        return $this->belongsTo(TarifaCategoria::class, 'tarifa_categoria_id');
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
        return $this->todas_las_imagenes_src[0] ?? asset('images/LOGOLORENTINA.png');
    }

    public function getTodasLasImagenesSrcAttribute(): array
    {
        $urls = [];
        $imagenes = $this->imagenes ?? [];

        if (empty($imagenes) && $this->imagen) {
            $imagenes = [$this->imagen];
        }

        if (empty($imagenes)) {
            $imagenes = $this->buscarImagenesLocalesCatalogo();
        }

        foreach ($imagenes as $img) {
            if (str_contains($img, 'drive.google.com')) {
                if (preg_match('/(?:id=|\/d\/)([a-zA-Z0-9_-]+)/', $img, $matches)) {
                    $driveId = $matches[1];
                    $urls[] = "https://drive.google.com/thumbnail?id={$driveId}&sz=w600";
                }
            } else {
                $img = $this->preferWebpForHeic($img);
                if (str_starts_with($img, 'http')) {
                    $urls[] = $img;
                } elseif (file_exists(public_path('images/' . $img))) {
                    $urls[] = asset('images/' . $img);
                }
            }
        }

        if (empty($urls)) {
            $urls[] = asset('images/LOGOLORENTINA.png');
        }

        return $urls;
    }

    private function buscarImagenesLocalesCatalogo(): array
    {
        $referencia = trim((string) $this->referencia);
        $color = trim((string) $this->color);

        if ($referencia === '') {
            return [];
        }

        $imagenes = self::catalogImageIndex()[$referencia] ?? [];
        if ($imagenes === []) {
            return [];
        }

        $colorTokens = $this->tokensImagen($color);
        $candidatos = [];

        foreach ($imagenes as $imagen) {
            $archivoTokens = $this->tokensImagen(pathinfo($imagen, PATHINFO_FILENAME));
            $coincidencias = count(array_intersect($colorTokens, $archivoTokens));

            if ($colorTokens !== [] && $coincidencias === 0) {
                continue;
            }

            $candidatos[] = [
                'imagen' => $imagen,
                'score' => ($coincidencias * 10) + (str_contains($this->normalizarTexto(pathinfo($imagen, PATHINFO_FILENAME)), $this->normalizarTexto($color)) ? 100 : 0),
            ];
        }

        usort($candidatos, fn (array $a, array $b): int => $b['score'] <=> $a['score']);

        $resueltos = array_values(array_unique(array_map(
            fn (array $candidato): string => $candidato['imagen'],
            $candidatos
        )));

        return $resueltos !== [] ? $resueltos : array_slice($imagenes, 0, 6);
    }

    /**
     * @return array<string, array<int, string>>
     */
    private static function catalogImageIndex(): array
    {
        if (self::$catalogImageIndex !== null) {
            return self::$catalogImageIndex;
        }

        $basePath = public_path('images/catalog');
        self::$catalogImageIndex = [];

        if (! is_dir($basePath)) {
            return self::$catalogImageIndex;
        }

        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($basePath));
        foreach ($iterator as $file) {
            if (! $file->isFile()) {
                continue;
            }

            $extension = strtolower($file->getExtension());
            if (! in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'heic'], true)) {
                continue;
            }

            $relative = str_replace('\\', '/', ltrim(substr($file->getPathname(), strlen(public_path('images'))), '\\/'));
            $referencia = basename(dirname($file->getPathname()));
            self::$catalogImageIndex[$referencia][] = $relative;

            if (preg_match('/^#?(Z?[0-9]+[A-Z]?)/i', $referencia, $folderMatches)) {
                $folderRef = strtoupper($folderMatches[1]);
                self::$catalogImageIndex[$folderRef][] = $relative;
                self::$catalogImageIndex[ltrim($folderRef, 'Z')][] = $relative;
                self::$catalogImageIndex['#' . ltrim($folderRef, 'Z')][] = $relative;
            }

            if (preg_match('/^#?(Z?[0-9]+[A-Z]?)/i', $file->getFilename(), $matches)) {
                $fileRef = strtoupper($matches[1]);
                self::$catalogImageIndex[$fileRef][] = $relative;
                self::$catalogImageIndex[ltrim($fileRef, 'Z')][] = $relative;
                self::$catalogImageIndex['#' . ltrim($fileRef, 'Z')][] = $relative;
            }
        }

        return self::$catalogImageIndex;
    }

    /**
     * @return array<int, string>
     */
    private function tokensImagen(string $value): array
    {
        $normalizado = $this->normalizarTexto($value);
        $tokens = preg_split('/[^A-Z0-9]+/', $normalizado) ?: [];

        $tokens = array_values(array_filter(
            array_unique($tokens),
            fn (string $token): bool => strlen($token) > 1 && ! in_array($token, ['PIE', 'TALON', 'CAPELLADA'], true) && $token !== (string) $this->referencia
        ));

        foreach ($tokens as $token) {
            if (strlen($token) > 3 && str_ends_with($token, 'S')) {
                $tokens[] = rtrim($token, 'S');
            }
        }

        return array_values(array_unique($tokens));
    }

    private function normalizarTexto(string $value): string
    {
        return strtoupper(Str::ascii($value));
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
