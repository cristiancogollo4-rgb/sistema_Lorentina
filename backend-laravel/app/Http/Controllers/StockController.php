<?php

namespace App\Http\Controllers;

use App\Models\InventarioZapato;
use App\Models\Producto;
use App\Support\XlsxWorkbook;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use RuntimeException;
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
            $registros = [];
            $hojaCabecera = $this->buscarHojaPreferida($workbook, ['CABECERA 2', 'CABECERA']);
            $hojaFabrica = $this->buscarHojaPreferida($workbook, ['FABRICA']);
            $hojaZara = $this->buscarHojaPorCoincidencia($workbook, ['ZARA', 'LOLA']);

            $rangoCabeceraPlanas = $this->resolverRangoActual($workbook, $hojaCabecera, 'PLANA');
            $rangoCabeceraPlataformas = $this->resolverRangoActual($workbook, $hojaCabecera, 'PLATAFORMA');
            $rangoFabricaPlanas = $this->resolverRangoActual($workbook, $hojaFabrica, 'PLANA');
            $rangoFabricaPlataformas = $this->resolverRangoActual($workbook, $hojaZara, 'PLATAFORMA');

            $c1 = $this->procesarHoja(
                $workbook,
                $hojaCabecera,
                'CABECERA',
                'PLANA',
                $registros,
                $rangoCabeceraPlanas
            );
            $c2 = $this->procesarHoja(
                $workbook,
                $hojaCabecera,
                'CABECERA',
                'PLATAFORMA',
                $registros,
                $rangoCabeceraPlataformas
            );
            $c3 = $this->procesarHoja(
                $workbook,
                $hojaFabrica,
                'FABRICA',
                'PLANA',
                $registros,
                $rangoFabricaPlanas
            );
            $c4 = $this->procesarHoja(
                $workbook,
                $hojaZara,
                'FABRICA',
                'PLATAFORMA',
                $registros,
                $rangoFabricaPlataformas
            );

            $registrosTotales = $this->construirRegistrosTotales($registros);
            $c5 = count($registrosTotales);

            DB::transaction(function () use ($registros, $registrosTotales): void {
                InventarioZapato::whereIn('sucursal', ['CABECERA', 'FABRICA', 'TOTAL'])->delete();

                $todosLosRegistros = array_merge(
                    array_values($registros),
                    array_values($registrosTotales)
                );

                $this->insertarRegistros($todosLosRegistros);
                $this->sincronizarProductosDesdeStock($registrosTotales);
            });

            $insertados = count($registros) + $c5;

            return response()->json([
                'mensaje' => 'Stock sincronizado',
                'detalles' => "Cargados: Cabecera planas ({$c1}), Cabecera plataformas ({$c2}), Fabrica planas ({$c3}), Fabrica plataformas ({$c4}), Total ({$c5}), Insertados ({$insertados})",
            ]);
        } catch (RuntimeException $error) {
            report($error);

            return response()->json(['error' => $error->getMessage()], 500);
        } catch (Throwable $error) {
            report($error);

            return response()->json(['error' => 'Error procesando el Excel'], 500);
        }
    }

    public function transferir(Request $request): JsonResponse
    {
        $data = $request->validate([
            'referencia' => ['required', 'string'],
            'color' => ['required', 'string'],
            'tipo' => ['required', 'string'],
            'origen' => ['required', 'in:CABECERA,FABRICA'],
            'destino' => ['required', 'in:CABECERA,FABRICA', 'different:origen'],
            'cantidades' => ['required', 'array'],
            'cantidades.*' => ['nullable', 'integer', 'min:0'],
        ]);

        $cantidades = [];
        foreach (range(35, 42) as $talla) {
            $cantidades[$talla] = (int) ($data['cantidades'][(string) $talla] ?? $data['cantidades'][$talla] ?? 0);
        }

        if (array_sum($cantidades) <= 0) {
            return response()->json(['error' => 'Debes indicar al menos un par para mover.'], 422);
        }

        DB::transaction(function () use ($data, $cantidades): void {
            $origen = InventarioZapato::query()
                ->where('referencia', $data['referencia'])
                ->where('color', $data['color'])
                ->where('tipo', $data['tipo'])
                ->where('sucursal', $data['origen'])
                ->lockForUpdate()
                ->first();

            if (! $origen) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'stock' => 'No existe stock en la sucursal de origen para este producto.',
                ]);
            }

            foreach ($cantidades as $talla => $cantidad) {
                $campo = "t{$talla}";
                if ((int) ($origen->{$campo} ?? 0) < $cantidad) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'stock' => "No hay suficientes pares en {$data['origen']} para la talla {$talla}.",
                    ]);
                }
            }

            $destino = InventarioZapato::query()->firstOrNew([
                'referencia' => $data['referencia'],
                'color' => $data['color'],
                'tipo' => $data['tipo'],
                'sucursal' => $data['destino'],
            ]);

            foreach ($cantidades as $talla => $cantidad) {
                $campo = "t{$talla}";
                $origen->{$campo} = (int) ($origen->{$campo} ?? 0) - $cantidad;
                $destino->{$campo} = (int) ($destino->{$campo} ?? 0) + $cantidad;
            }

            $origen->total = $this->sumarTotalInventario($origen);
            $origen->updated_at = now();
            $origen->save();

            $destino->total = $this->sumarTotalInventario($destino);
            $destino->updated_at = now();
            $destino->save();

            $this->recalcularTotalProducto($data['referencia'], $data['color'], $data['tipo']);
        });

        return response()->json(['mensaje' => 'Stock transferido correctamente.']);
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
     * @param array<int, string> $preferencias
     */
    private function buscarHojaPreferida(XlsxWorkbook $workbook, array $preferencias): ?string
    {
        foreach ($preferencias as $nombre) {
            $sheetName = $this->buscarHoja($workbook, $nombre);
            if ($sheetName !== null) {
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

    /**
     * @return array{start_row:int, end_row:int}|null
     */
    private function resolverRangoActual(XlsxWorkbook $workbook, ?string $sheetName, string $tipoEsperado): ?array
    {
        if ($sheetName === null) {
            return null;
        }

        $rows = $workbook->getSheetRowsIndexed($sheetName);
        $ranges = $workbook->getSheetSumRanges($sheetName);
        $candidatos = [];

        foreach ($ranges as $range) {
            $tipoRange = $this->inferirTipoRango($rows, $range['start_row'], $range['end_row']);

            if ($tipoRange !== $tipoEsperado) {
                continue;
            }

            $candidatos[] = [
                'start_row' => $range['start_row'],
                'end_row' => $range['end_row'],
            ];
        }

        usort(
            $candidatos,
            fn (array $a, array $b) => $a['end_row'] <=> $b['end_row']
        );

        if ($candidatos !== []) {
            return $candidatos[array_key_last($candidatos)];
        }

        return null;
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
        array &$registros = [],
        ?array $rango = null
    ): int {
        if (! $sheetName) {
            return 0;
        }

        $rawData = $workbook->getSheetRowsIndexed($sheetName);
        $contador = 0;

        foreach ($rawData as $rowNumber => $row) {
            if ($row === []) {
                continue;
            }

            if (
                $rango !== null &&
                ($rowNumber < $rango['start_row'] || $rowNumber > $rango['end_row'])
            ) {
                continue;
            }

            $refColor = trim((string) ($row[0] ?? ''));
            if ($refColor === '') {
                continue;
            }

            $texto = strtoupper(preg_replace('/\s+/', ' ', $refColor) ?? $refColor);

            if ($this->debeIgnorarFila($texto)) {
                continue;
            }

            [$referencia, $color] = $this->parsearReferenciaYColor($refColor);

            if (strlen($referencia) <= 1) {
                continue;
            }

            $tipo = $forzarTipo ?? 'PLANA';
            if ($forzarTipo === null && (
                str_starts_with($referencia, 'Z') ||
                str_starts_with($referencia, 'LOLAS') ||
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

            $this->agregarRegistro($registros, [
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
            ]);

            $contador++;
        }

        return $contador;
    }

    /**
     * @param array<string, array<string, mixed>> $registros
     */
    private function construirRegistrosTotales(array $registros): array
    {
        $totales = [];

        foreach ($registros as $registro) {
            $registroTotal = $registro;
            $registroTotal['sucursal'] = 'TOTAL';
            $registroTotal['updated_at'] = now();

            $this->agregarRegistro($totales, $registroTotal);
        }

        return $totales;
    }

    /**
     * @param array<string, array<string, mixed>> $registros
     * @param array<string, mixed> $registro
     */
    private function agregarRegistro(array &$registros, array $registro): void
    {
        $key = implode('|', [
            (string) $registro['referencia'],
            (string) $registro['color'],
            (string) $registro['sucursal'],
            (string) $registro['tipo'],
        ]);

        if (! isset($registros[$key])) {
            $registros[$key] = $registro;

            return;
        }

        foreach (['t35', 't36', 't37', 't38', 't39', 't40', 't41', 't42', 'total'] as $campo) {
            $registros[$key][$campo] = (int) $registros[$key][$campo] + (int) $registro[$campo];
        }

        $registros[$key]['updated_at'] = $registro['updated_at'];
    }

    private function debeIgnorarFila(string $texto): bool
    {
        return
            str_contains($texto, 'REF Y COLOR') ||
            str_contains($texto, 'STOCK') ||
            str_contains($texto, 'ENTRADAS') ||
            str_contains($texto, 'NO TOCAR') ||
            str_contains($texto, 'MUESTRA PARA VENTA') ||
            str_contains($texto, 'PLATAFORMAS ZARA') ||
            str_starts_with($texto, 'TOTAL');
    }

    /**
     * @param array<int, array<int, mixed>> $rows
     */
    private function inferirTipoRango(array $rows, int $startRow, int $endRow): string
    {
        $registrosValidos = 0;
        $registrosPlataforma = 0;

        foreach ($rows as $rowNumber => $row) {
            if ($rowNumber < $startRow || $rowNumber > $endRow) {
                continue;
            }

            $refColor = trim((string) ($row[0] ?? ''));
            if ($refColor === '') {
                continue;
            }

            [$referencia] = $this->parsearReferenciaYColor($refColor);
            if (strlen($referencia) <= 1) {
                continue;
            }

            $registrosValidos++;

            if (
                str_starts_with($referencia, 'Z') ||
                str_starts_with($referencia, 'LOLAS') ||
                str_starts_with($referencia, 'LOLA') ||
                str_contains($referencia, 'TENIS')
            ) {
                $registrosPlataforma++;
            }
        }

        if ($registrosValidos > 0 && $registrosPlataforma >= (int) ceil($registrosValidos / 2)) {
            return 'PLATAFORMA';
        }

        return 'PLANA';
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function parsearReferenciaYColor(string $refColor): array
    {
        $partes = preg_split('/\s+/', trim($refColor)) ?: [];
        $referencia = strtoupper($partes[0] ?? '');
        $offsetColor = 1;

        if (
            isset($partes[1]) &&
            preg_match('/^[A-Z]+$/', $referencia) === 1 &&
            preg_match('/^\d+[A-Z]?$/i', $partes[1]) === 1
        ) {
            $referencia .= ' ' . strtoupper($partes[1]);
            $offsetColor = 2;
        }

        $color = trim(implode(' ', array_slice($partes, $offsetColor))) ?: 'UNICO';

        return [$referencia, $color];
    }

    /**
     * @param array<int, array<string, mixed>> $registros
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

    /**
     * @param array<string, array<string, mixed>> $registrosTotales
     */
    private function sincronizarProductosDesdeStock(array $registrosTotales): void
    {
        foreach ($registrosTotales as $registro) {
            if (((int) ($registro['total'] ?? 0)) <= 0) {
                continue;
            }

            $referencia = trim((string) ($registro['referencia'] ?? ''));
            $color = trim((string) ($registro['color'] ?? 'UNICO'));
            $tipo = trim((string) ($registro['tipo'] ?? 'PLANA'));

            if ($referencia === '') {
                continue;
            }

            Producto::query()->updateOrCreate(
                [
                    'referencia' => $referencia,
                    'color' => $color,
                    'tipo' => $tipo,
                ],
                [
                    'nombre_modelo' => trim($referencia . ' - ' . $color),
                    'descripcion' => "Producto sincronizado desde stock {$tipo}",
                    'precio_detal' => 0,
                    'precio_mayor' => 0,
                    'costo_produccion' => 0,
                    'activo' => true,
                ]
            );
        }
    }

    private function recalcularTotalProducto(string $referencia, string $color, string $tipo): void
    {
        $cabecera = InventarioZapato::query()
            ->where('referencia', $referencia)
            ->where('color', $color)
            ->where('tipo', $tipo)
            ->where('sucursal', 'CABECERA')
            ->first();
        $fabrica = InventarioZapato::query()
            ->where('referencia', $referencia)
            ->where('color', $color)
            ->where('tipo', $tipo)
            ->where('sucursal', 'FABRICA')
            ->first();

        $total = InventarioZapato::query()->firstOrNew([
            'referencia' => $referencia,
            'color' => $color,
            'tipo' => $tipo,
            'sucursal' => 'TOTAL',
        ]);

        foreach (range(35, 42) as $talla) {
            $campo = "t{$talla}";
            $total->{$campo} = (int) ($cabecera?->{$campo} ?? 0) + (int) ($fabrica?->{$campo} ?? 0);
        }

        $total->total = $this->sumarTotalInventario($total);
        $total->updated_at = now();
        $total->save();
    }

    private function sumarTotalInventario(InventarioZapato $inventario): int
    {
        return array_sum(array_map(
            fn (int $talla) => (int) ($inventario->{"t{$talla}"} ?? 0),
            range(35, 42)
        ));
    }
}
