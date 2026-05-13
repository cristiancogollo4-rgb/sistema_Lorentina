<?php

namespace App\Support;

class ProductoPrecio
{
    public const DETAL_PLANA = 200000;
    public const DETAL_PLATAFORMA = 240000;
    public const MAYOR_PLANA = 102000;
    public const MAYOR_PLATAFORMA = 116000;

    /**
     * @return array{detal:int, mayor:int}
     */
    public static function para(string $tipo = 'PLANA', ?string $categoria = null): array
    {
        $tipo = strtoupper(trim($tipo));
        $categoria = strtoupper(trim((string) $categoria));
        $esPlataforma = $tipo === 'PLATAFORMA' || str_contains($categoria, 'PLATAFORMA') || str_contains($categoria, 'ZARA');

        return [
            'detal' => $esPlataforma ? self::DETAL_PLATAFORMA : self::DETAL_PLANA,
            'mayor' => $esPlataforma ? self::MAYOR_PLATAFORMA : self::MAYOR_PLANA,
        ];
    }
}
