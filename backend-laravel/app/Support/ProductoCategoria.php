<?php

namespace App\Support;

use App\Models\TarifaCategoria;

class ProductoCategoria
{
    public static function nombreSugerido(string $referencia, string $tipo = 'PLANA'): string
    {
        $referencia = strtoupper(trim($referencia));
        $tipo = strtoupper(trim($tipo));

        if ($tipo === 'PLATAFORMA' || str_starts_with($referencia, 'Z')) {
            return 'PLATAFORMA / ZARA';
        }

        if (str_starts_with($referencia, 'LOLAS') || str_starts_with($referencia, 'LOLA')) {
            return 'LOLAS';
        }

        if (str_contains($referencia, 'TENIS') || str_starts_with($referencia, 'P')) {
            return 'TENIS';
        }

        if (in_array($referencia, ['1016', '1024', '1028', '1029', '1035', '1041', '1056', '1157', '1187', '1195'], true)) {
            return 'ROMANA';
        }

        return 'CLASICA';
    }

    public static function idSugerido(string $referencia, string $tipo = 'PLANA'): ?int
    {
        $nombre = self::nombreSugerido($referencia, $tipo);

        return TarifaCategoria::query()->where('nombre', $nombre)->value('id')
            ?? TarifaCategoria::query()->orderBy('id')->value('id');
    }
}
