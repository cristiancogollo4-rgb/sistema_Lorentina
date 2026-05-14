<?php

namespace App\Support;

use App\Models\Producto;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\File;

class ProductoCatalog
{
    /** @var array<int, array<string, mixed>>|null */
    private static ?array $items = null;

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function all(): array
    {
        if (self::$items !== null) {
            return self::$items;
        }

        $path = database_path('seeders/data/productos_link_catalog.json');

        if (! File::exists($path)) {
            self::$items = [];

            return self::$items;
        }

        $items = json_decode(File::get($path), true);
        self::$items = is_array($items) ? $items : [];

        return self::$items;
    }

    public static function find(string $referencia, string $color, string $tipo): ?array
    {
        $referencia = self::normalize($referencia);
        $color = self::normalize($color);
        $tipo = self::normalize($tipo);

        $key = "{$referencia}|{$color}|{$tipo}";

        foreach (self::all() as $item) {
            if (self::itemKey($item) === $key) {
                return $item;
            }
        }

        // Fallback: Try matching by reference and type, and check if color is a partial match
        foreach (self::all() as $item) {
            $itemRef = self::normalize((string) ($item['referencia'] ?? ''));
            $itemTipo = self::normalize((string) ($item['tipo'] ?? 'PLANA'));
            $itemColor = self::normalize((string) ($item['color'] ?? ''));

            if ($itemRef === $referencia && $itemTipo === $tipo) {
                // Check if one color contains the other (e.g. "ACEITUNA" vs "VERDE ACEITUNA")
                if (str_contains($itemColor, $color) || str_contains($color, $itemColor)) {
                    return $item;
                }
            }
        }

        return null;
    }

    public static function isAllowed(string $referencia, string $color, string $tipo): bool
    {
        return self::find($referencia, $color, $tipo) !== null;
    }

    public static function imageUrlFor(string $referencia, string $color, string $tipo): ?string
    {
        $item = self::find($referencia, $color, $tipo);

        return $item ? self::bestImageUrl($item) : null;
    }

    public static function applyToProduct(Producto $producto): Producto
    {
        $item = self::find(
            (string) $producto->referencia,
            (string) $producto->color,
            (string) $producto->tipo
        );

        if (! $item) {
            return $producto;
        }

        $producto->nombre_modelo = (string) ($item['product'] ?? $producto->nombre_modelo);
        
        $imageUrl = self::bestImageUrl($item);

        if ($imageUrl) {
            $producto->imagen = $imageUrl;
        }

        if (!empty($item['images'])) {
            $producto->imagenes = $item['images'];
        }

        return $producto;
    }

    public static function applyCatalogFilter(Builder $query, bool $onlyWithImage = false): Builder
    {
        $items = array_values(array_filter(
            self::all(),
            fn (array $item): bool => ! $onlyWithImage || ! empty($item['image_url'])
        ));

        if ($items === []) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where(function (Builder $query) use ($items): void {
            foreach ($items as $item) {
                $query->orWhere(function (Builder $query) use ($item): void {
                    $query
                        ->where('productos.referencia', (string) ($item['referencia'] ?? ''))
                        ->where('productos.color', (string) ($item['color'] ?? ''))
                        ->where('productos.tipo', (string) ($item['tipo'] ?? 'PLANA'));
                });
            }
        });
    }

    public static function replacementFor(string $referencia, string $color, string $tipo): ?array
    {
        $referencia = self::normalize($referencia);
        $tipo = self::normalize($tipo);

        foreach (self::all() as $item) {
            if (
                self::normalize((string) ($item['referencia'] ?? '')) === $referencia &&
                self::normalize((string) ($item['tipo'] ?? '')) === $tipo
            ) {
                return $item;
            }
        }

        foreach (self::all() as $item) {
            if (self::normalize((string) ($item['referencia'] ?? '')) === $referencia) {
                return $item;
            }
        }

        return self::all()[0] ?? null;
    }

    public static function productKey(string $referencia, string $color, string $tipo): string
    {
        return self::normalize("{$referencia}|{$color}|{$tipo}");
    }

    /**
     * @param array<string, mixed> $item
     */
    public static function itemKey(array $item): string
    {
        return self::productKey(
            (string) ($item['referencia'] ?? ''),
            (string) ($item['color'] ?? ''),
            (string) ($item['tipo'] ?? 'PLANA')
        );
    }

    /**
     * @param array<string, mixed> $item
     */
    public static function bestImageUrl(array $item): ?string
    {
        return self::imageUrls($item)[0] ?? null;
    }

    /**
     * @param array<string, mixed> $item
     * @return array<int, string>
     */
    public static function imageUrls(array $item): array
    {
        $sourceFiles = $item['source_files'] ?? [];
        $links = $item['all_links'] ?? [];
        $urls = [];

        if (is_array($sourceFiles) && is_array($links)) {
            foreach ($sourceFiles as $index => $sourceFile) {
                if (! self::isImageFile((string) $sourceFile)) {
                    continue;
                }

                $link = $links[$index] ?? null;

                if (is_string($link) && $link !== '') {
                    $urls[] = self::thumbnailUrl($link);
                }
            }
        }

        if (isset($item['image_url']) && is_string($item['image_url'])) {
            $urls[] = self::thumbnailUrl($item['image_url']);
        }

        return array_values(array_unique($urls));
    }

    private static function isImageFile(string $filename): bool
    {
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        return in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true);
    }

    private static function thumbnailUrl(string $url): string
    {
        if (preg_match('/(?:id=|\/d\/)([a-zA-Z0-9_-]+)/', $url, $matches)) {
            return "https://drive.google.com/thumbnail?id={$matches[1]}&sz=w1000";
        }

        return $url;
    }

    public static function normalize(string $value): string
    {
        $value = trim(preg_replace('/\s+/', ' ', $value) ?? $value);
        // Remove # prefix if exists
        $value = ltrim($value, '#');
        
        $value = strtr($value, [
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u',
            'Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U',
        ]);

        return function_exists('mb_strtoupper')
            ? mb_strtoupper($value, 'UTF-8')
            : strtoupper($value);
    }
}
