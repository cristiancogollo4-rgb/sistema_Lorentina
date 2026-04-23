<?php

namespace App\Http\Controllers;

use App\Models\InventarioZapato;
use App\Support\XlsxWorkbook;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class StockController extends Controller
{
    private const UPSERT_CHUNK_SIZE = 250;

    public function index(Request $request): JsonResponse
    {
        $query = InventarioZapato::query()->orderBy('referencia');

        if ($request->filled('sucursal')) {
            $query->where('sucursal', (string) $request->query('sucursal'));
        }

        return response()->json($query->get());
    }

    public function masivo(Request $request): JsonResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx'],
        ]);

        try {
            set_time_limit(0);

            $workbook = new XlsxWorkbook($request->file('file')->getRealPath());

            InventarioZapato::whereIn('sucursal', ['CABECERA', 'FABRICA', 'TOTAL'])->delete();

            $registros = [];

            $c1 = $this->procesarHoja($workbook, $this->buscarHoja($workbook, 'CABECERA'), 'CABECERA', null, $registros);
            $c2 = $this->procesarHoja($workbook, $this->buscarHoja($workbook, 'FABRICA'), 'FABRICA', 'PLANA', $registros);
            $hojaZara = $this->buscarHojaPorCoincidencia($workbook, ['ZARA', 'LOLA']);
            $c3 = $this->procesarHoja($workbook, $hojaZara, 'FABRICA', 'PLATAFORMA', $registros);
            $c4 = $this->procesarHoja($workbook, $this->buscarHoja($workbook, 'TOTAL'), 'TOTAL', null, $registros);

            $insertados = $this->insertarRegistros($registros);

            return response()->json([
                'mensaje' => 'Stock sincronizado',
                'detalles' => "Cargados: Cabecera ({$c1}), Fabrica (" . ($c2 + $c3) . "), Total ({$c4}), Insertados ({$insertados})",
            ]);
        } catch (Throwable $error) {
            report($error);

            return response()->json(['error' => 'Error procesando el Excel'], 500);
        }
    }

    private function buscarHoja(XlsxWorkbook $workbook, string $nombre): ?string
    {
        $nombreLimpio = $this->normalizeSheetName($nombre);

        foreach ($workbook->getSheetNames() as $sheetName) {
            if (str_contains($this->normalizeSheetName($sheetName), $nombreLimpio)) {
                return $sheetName;
            }
        }

        return null;
    }

    /**
     * @param array<int, string> $coincidencias
     */
    private function buscarHojaPorCoincidencia(XlsxWorkbook $workbook, array $coincidencias): ?string
    {
        foreach ($workbook->getSheetNames() as $sheetName) {
            $upper = strtoupper($sheetName);
            foreach ($coincidencias as $needle) {
                if (str_contains($upper, strtoupper($needle))) {
                    return $sheetName;
                }
            }
        }

        return null;
    }

    private function normalizeSheetName(string $value): string
    {
        return strtoupper(str_replace(' ', '', $value));
    }

    private function parseIntSafe(mixed $value): int
    {
        if ($value === null || $value === '') {
            return 0;
        }

        $parsed = (int) $value;

        return is_numeric((string) $value) ? $parsed : 0;
    }

    private function procesarHoja(
        XlsxWorkbook $workbook,
        ?string $sheetName,
        string $sucursalAsignada,
        ?string $forzarTipo = null,
        array &$registros = []
    ): int {
        if (! $sheetName) {
            return 0;
        }

        $rawData = $workbook->getSheetRows($sheetName);
        $contador = 0;

        foreach ($rawData as $row) {
            if ($row === []) {
                continue;
            }

            $refColor = $row[0] ?? null;
            if (! is_string($refColor) || trim($refColor) === '') {
                continue;
            }

            $texto = strtoupper($refColor);

            if (
                str_contains($texto, 'REF Y COLOR') ||
                str_contains($texto, 'STOCK') ||
                str_contains($texto, 'ENTRADAS') ||
                str_starts_with($texto, 'TOTAL')
            ) {
                continue;
            }

            $meses = ['ENERO', 'FEBRERO', 'MARZO', 'ABRIL', 'MAYO', 'JUNIO', 'JULIO', 'AGOSTO', 'SEPTIEMBRE', 'OCTUBRE', 'NOVIEMBRE', 'DICIEMBRE'];
            $esFilaMes = collect($meses)->contains(fn (string $mes) => str_contains($texto, $mes) && strlen($texto) < 15);
            if ($esFilaMes) {
                continue;
            }

            $partes = preg_split('/\s+/', trim($refColor)) ?: [];
            $referencia = strtoupper($partes[0] ?? '');
            $color = trim(implode(' ', array_slice($partes, 1))) ?: 'UNICO';

            if (strlen($referencia) <= 1) {
                continue;
            }

            $tipo = $forzarTipo ?? 'PLANA';
            if ($forzarTipo === null && (
                str_starts_with($referencia, 'Z') ||
                str_starts_with($referencia, 'LOLA') ||
                str_contains($referencia, 'TENIS') ||
                str_starts_with($referencia, 'P')
            )) {
                $tipo = 'PLATAFORMA';
            }

            $t35 = $this->parseIntSafe($row[2] ?? null);
            $t36 = $this->parseIntSafe($row[3] ?? null);
            $t37 = $this->parseIntSafe($row[4] ?? null);
            $t38 = $this->parseIntSafe($row[5] ?? null);
            $t39 = $this->parseIntSafe($row[6] ?? null);
            $t40 = $this->parseIntSafe($row[7] ?? null);
            $t41 = $this->parseIntSafe($row[8] ?? null);
            $t42 = $this->parseIntSafe($row[9] ?? null);
            $total = $t35 + $t36 + $t37 + $t38 + $t39 + $t40 + $t41 + $t42;

            $key = implode('|', [$referencia, $color, $sucursalAsignada]);

            $registros[$key] = [
                'referencia' => $referencia,
                'color' => $color,
                'sucursal' => $sucursalAsignada,
                'tipo' => $tipo,
                't35' => $t35,
                't36' => $t36,
                't37' => $t37,
                't38' => $t38,
                't39' => $t39,
                't40' => $t40,
                't41' => $t41,
                't42' => $t42,
                'total' => $total,
                'updated_at' => now(),
            ];

            $contador++;
        }

        return $contador;
    }

    /**
     * @param array<string, array<string, mixed>> $registros
     */
    private function insertarRegistros(array $registros): int
    {
        if ($registros === []) {
            return 0;
        }

        $chunks = array_chunk(array_values($registros), self::UPSERT_CHUNK_SIZE);
        $insertados = 0;

        foreach ($chunks as $chunk) {
            DB::table('inventario_zapatos')->insert($chunk);
            $insertados += count($chunk);
        }

        return $insertados;
    }
}
