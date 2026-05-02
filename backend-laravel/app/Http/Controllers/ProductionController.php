<?php

namespace App\Http\Controllers;

use App\Models\InventarioZapato;
use App\Models\InventarioMovimiento;
use App\Models\OrdenProduccion;
use App\Models\Producto;
use App\Models\TarifaCategoria;
use App\Models\User;
use App\Models\Venta;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductionController extends Controller
{
    public function empleadosCorte(): JsonResponse
    {
        return response()->json(
            User::query()
                ->where('rol', 'CORTE')
                ->where('activo', true)
                ->orderBy('nombre')
                ->get()
                ->makeHidden('password')
        );
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'referencia' => ['required', 'string'],
            'color' => ['required', 'string'],
            'categoria' => ['nullable', 'string'],
            'materiales' => ['required', 'string'],
            'observacion' => ['nullable', 'string'],
            'destino' => ['required', 'string'],
            'cortadorId' => ['nullable', 'integer'],
            'precioManualCorte' => ['nullable', 'numeric'],
            'precioManualArmado' => ['nullable', 'numeric'],
            'precioManualCostura' => ['nullable', 'numeric'],
            'precioManualSoladura' => ['nullable', 'numeric'],
            'precioManualEmplantillado' => ['nullable', 'numeric'],
            't34' => ['nullable', 'numeric'],
            't35' => ['nullable', 'numeric'],
            't36' => ['nullable', 'numeric'],
            't37' => ['nullable', 'numeric'],
            't38' => ['nullable', 'numeric'],
            't39' => ['nullable', 'numeric'],
            't40' => ['nullable', 'numeric'],
            't41' => ['nullable', 'numeric'],
            't42' => ['nullable', 'numeric'],
            't43' => ['nullable', 'numeric'],
            't44' => ['nullable', 'numeric'],
        ]);

        $categoria = $data['categoria'] ?? 'ROMANA';
        $precios = $this->resolverPrecios($categoria, $data);

        $totalPares = 0;
        $tallas = [];
        foreach (range(34, 44) as $talla) {
            $valor = (int) ($data["t{$talla}"] ?? 0);
            $tallas["t{$talla}"] = $valor;
            $totalPares += $valor;
        }

        $orden = OrdenProduccion::create([
            'numero_orden' => 'OP-' . substr((string) round(microtime(true) * 1000), -6),
            'referencia' => $data['referencia'],
            'color' => $data['color'],
            'categoria' => $categoria,
            'precio_corte' => $precios['corte'],
            'precio_armado' => $precios['armado'],
            'precio_costura' => $precios['costura'],
            'precio_soladura' => $precios['soladura'],
            'precio_emplantillado' => $precios['emplantillado'],
            'materiales' => $data['materiales'],
            'observacion' => $data['observacion'] ?? null,
            'destino' => $data['destino'],
            'cortador_id' => $data['cortadorId'] ?? null,
            'total_pares' => $totalPares,
            'estado' => 'EN_CORTE',
            ...$tallas,
        ]);

        return response()->json([
            'msg' => 'Orden creada con precios desglosados',
            'orden' => $orden->numero_orden,
            'precioAplicado' => $precios['corte'],
        ]);
    }

    public function tablero(Request $request): JsonResponse
    {
        $rango = $request->query('rango', 'produccion');
        $fechaInicio = $request->query('inicio');
        $fechaFin = $request->query('fin');
        
        $queryOrdenes = OrdenProduccion::query()
            ->whereNotIn('estado', ['TERMINADO', 'EN_STOCK']);
        $qCreadas = OrdenProduccion::query();
        $qTerminadas = OrdenProduccion::query()->where('estado', 'EN_STOCK');

        if ($rango === 'produccion') {
            // Sin filtro de fechas para órdenes activas:
            // mostrar desde la orden en producción más antigua hasta la más reciente.
        } elseif ($rango === 'semana') {
            $inicio = now()->startOfWeek();
            $queryOrdenes->where('fecha_inicio', '>=', $inicio);
            $qCreadas->where('fecha_inicio', '>=', $inicio);
            $qTerminadas->where('fecha_fin_terminado', '>=', $inicio);
        } elseif ($rango === 'mes') {
            $inicio = now()->startOfMonth();
            $queryOrdenes->where('fecha_inicio', '>=', $inicio);
            $qCreadas->where('fecha_inicio', '>=', $inicio);
            $qTerminadas->where('fecha_fin_terminado', '>=', $inicio);
        } elseif ($rango === 'custom' && $fechaInicio && $fechaFin) {
            $dInicio = \Carbon\Carbon::parse($fechaInicio)->startOfDay();
            $dFin = \Carbon\Carbon::parse($fechaFin)->endOfDay();
            $queryOrdenes->whereBetween('fecha_inicio', [$dInicio, $dFin]);
            $qCreadas->whereBetween('fecha_inicio', [$dInicio, $dFin]);
            $qTerminadas->whereBetween('fecha_fin_terminado', [$dInicio, $dFin]);
        }

        $ordenes = $queryOrdenes->orderByDesc('id')
            ->get()
            ->map(fn (OrdenProduccion $orden) => $this->formatOrden($orden));

        $empleados = User::query()
            ->where('rol', '!=', 'ADMIN')
            ->where('activo', true)
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'apellido', 'rol']);

        $paresFabricar = (int) $qCreadas->sum('total_pares');
        $paresStock = (int) $qTerminadas->sum('total_pares');
        $inicioSemana = now()->startOfWeek();
        $inicioMes = now()->startOfMonth();
        $ventasSemana = (float) Venta::query()->where('fecha_venta', '>=', $inicioSemana)->sum('total');
        $ventasMes = (float) Venta::query()->where('fecha_venta', '>=', $inicioMes)->sum('total');

        return response()->json([
            'ordenes' => $ordenes,
            'empleados' => $empleados,
            'stats' => [
                'paresFabricar' => $paresFabricar,
                'paresStock' => $paresStock,
                'ventasSemana' => $ventasSemana,
                'ventasMes' => $ventasMes,
            ]
        ]);
    }

    public function asignar(Request $request): JsonResponse
    {
        $data = $request->validate([
            'ordenId' => ['required', 'integer'],
            'empleadoId' => ['required', 'integer'],
            'rol' => ['required', 'string'],
            'nuevoEstado' => ['required', 'string'],
        ]);

        $orden = OrdenProduccion::findOrFail($data['ordenId']);

        $actualizacion = ['estado' => $data['nuevoEstado']];
        if ($data['rol'] === 'ARMADOR') {
            $actualizacion['armador_id'] = $data['empleadoId'];
        } elseif ($data['rol'] === 'COSTURERO') {
            $actualizacion['costurero_id'] = $data['empleadoId'];
        } elseif ($data['rol'] === 'SOLADOR') {
            $actualizacion['solador_id'] = $data['empleadoId'];
        } elseif ($data['rol'] === 'EMPLANTILLADOR') {
            $actualizacion['emplantillador_id'] = $data['empleadoId'];
        }

        $orden->update($actualizacion);

        return response()->json(['mensaje' => 'Asignación exitosa']);
    }

    public function misTareas(int $empleadoId): JsonResponse
    {
        $tareas = OrdenProduccion::query()
            ->where(function ($query) use ($empleadoId) {
                $query->where(fn ($q) => $q->where('cortador_id', $empleadoId)->where('estado', 'EN_CORTE'))
                    ->orWhere(fn ($q) => $q->where('armador_id', $empleadoId)->where('estado', 'EN_ARMADO'))
                    ->orWhere(fn ($q) => $q->where('costurero_id', $empleadoId)->where('estado', 'EN_COSTURA'))
                    ->orWhere(fn ($q) => $q->where('solador_id', $empleadoId)->where('estado', 'EN_SOLADURA'))
                    ->orWhere(fn ($q) => $q->where('emplantillador_id', $empleadoId)->where('estado', 'EN_EMPLANTILLADO'));
            })
            ->orderByDesc('id')
            ->get()
            ->map(fn (OrdenProduccion $orden) => $this->formatOrden($orden));

        return response()->json($tareas);
    }

    public function terminarTarea(Request $request): JsonResponse
    {
        $data = $request->validate([
            'ordenId' => ['required', 'integer'],
            'rol' => ['required', 'string'],
        ]);

        $rol = strtoupper($data['rol']);
        $orden = OrdenProduccion::findOrFail($data['ordenId']);
        $ahora = now();

        if ($rol === 'CORTE') {
            $orden->update(['estado' => 'EN_ARMADO', 'fecha_fin_corte' => $ahora]);
            $nuevoEstado = 'EN_ARMADO';
        } elseif (in_array($rol, ['ARMADOR', 'ARMADO'], true)) {
            $orden->update(['estado' => 'EN_COSTURA', 'fecha_fin_armado' => $ahora]);
            $nuevoEstado = 'EN_COSTURA';
        } elseif (in_array($rol, ['COSTURERO', 'COSTURA'], true)) {
            $orden->update(['estado' => 'EN_SOLADURA', 'fecha_fin_costura' => $ahora]);
            $nuevoEstado = 'EN_SOLADURA';
        } elseif (in_array($rol, ['SOLADOR', 'SOLADURA'], true)) {
            $orden->update(['estado' => 'EN_EMPLANTILLADO', 'fecha_fin_soladura' => $ahora]);
            $nuevoEstado = 'EN_EMPLANTILLADO';
        } elseif ($rol === 'EMPLANTILLADOR') {
            $estadoFinal = $this->debeEsperarIngresoStock($orden) ? 'LISTO_PARA_STOCK' : 'TERMINADO';

            $orden->update([
                'estado' => $estadoFinal,
                'fecha_fin_emplantillado' => $ahora,
                'fecha_fin_terminado' => $ahora,
            ]);
            $nuevoEstado = $estadoFinal;
        } else {
            return response()->json(['error' => 'Rol no válido'], 400);
        }

        return response()->json([
            'mensaje' => '¡Tarea terminada con éxito!',
            'nuevoEstado' => $nuevoEstado,
        ]);
    }

    public function pasarAStock(Request $request): JsonResponse
    {
        $data = $request->validate([
            'ordenId' => ['required', 'integer'],
        ]);

        $orden = OrdenProduccion::findOrFail($data['ordenId']);

        if (! $this->debeEsperarIngresoStock($orden)) {
            return response()->json([
                'error' => 'Solo las ordenes destinadas a stock se pueden ingresar desde este flujo.',
            ], 422);
        }

        if ($orden->estado !== 'LISTO_PARA_STOCK') {
            return response()->json([
                'error' => 'La orden todavia no esta lista para pasar a stock.',
            ], 422);
        }

        if ((int) $orden->t34 > 0 || (int) $orden->t43 > 0 || (int) $orden->t44 > 0) {
            return response()->json([
                'error' => 'La tabla de inventario actual solo soporta tallas 35 a 42. Ajusta primero esas tallas para esta orden.',
            ], 422);
        }

        DB::transaction(function () use ($orden): void {
            $tipo = $this->inferirTipoProducto((string) $orden->referencia);

            Producto::query()->updateOrCreate(
                [
                    'referencia' => $orden->referencia,
                    'color' => $orden->color,
                    'tipo' => $tipo,
                ],
                [
                    'nombre_modelo' => trim($orden->referencia . ' - ' . $orden->color),
                    'descripcion' => "Producto creado desde orden {$orden->numero_orden}",
                    'precio_detal' => 0,
                    'precio_mayor' => 0,
                    'costo_produccion' => 0,
                    'activo' => true,
                ]
            );

            $this->sumarOrdenAInventario($orden, 'CABECERA', $tipo);
            $this->sumarOrdenAInventario($orden, 'TOTAL', $tipo);
            $this->registrarMovimientoIngresoOrden($orden, 'CABECERA', $tipo);

            $orden->update(['estado' => 'EN_STOCK']);
        });

        return response()->json([
            'mensaje' => 'Orden ingresada a stock correctamente.',
            'nuevoEstado' => 'EN_STOCK',
        ]);
    }

    public function nomina(Request $request, int $empleadoId): JsonResponse
    {
        $rol = strtoupper((string) $request->query('rol', ''));
        $query = OrdenProduccion::query();
        $orderColumn = 'id';

        switch ($rol) {
            case 'CORTE':
                $query->where('cortador_id', $empleadoId)->whereNotNull('fecha_fin_corte');
                $orderColumn = 'fecha_fin_corte';
                break;
            case 'ARMADO':
                $query->where('armador_id', $empleadoId)->whereNotNull('fecha_fin_armado');
                $orderColumn = 'fecha_fin_armado';
                break;
            case 'COSTURA':
                $query->where('costurero_id', $empleadoId)->whereNotNull('fecha_fin_costura');
                $orderColumn = 'fecha_fin_costura';
                break;
            case 'SOLADURA':
                $query->where('solador_id', $empleadoId)->whereNotNull('fecha_fin_soladura');
                $orderColumn = 'fecha_fin_soladura';
                break;
            case 'EMPLANTILLADO':
                $query->where('emplantillador_id', $empleadoId)->whereNotNull('fecha_fin_emplantillado');
                $orderColumn = 'fecha_fin_emplantillado';
                break;
            default:
                return response()->json(['totalGanado' => 0, 'detalle' => []]);
        }

        $ordenes = $query->orderByDesc($orderColumn)->get();

        $totalGanado = 0;
        $detalle = $ordenes->map(function (OrdenProduccion $orden) use ($rol, &$totalGanado) {
            $precio = 0;
            $fecha = null;

            if ($rol === 'CORTE') {
                $precio = $orden->precio_corte;
                $fecha = $orden->fecha_fin_corte;
            } elseif ($rol === 'ARMADO') {
                $precio = $orden->precio_armado;
                $fecha = $orden->fecha_fin_armado;
            } elseif ($rol === 'COSTURA') {
                $precio = $orden->precio_costura;
                $fecha = $orden->fecha_fin_costura;
            } elseif ($rol === 'SOLADURA') {
                $precio = $orden->precio_soladura;
                $fecha = $orden->fecha_fin_soladura;
            } elseif ($rol === 'EMPLANTILLADO') {
                $precio = $orden->precio_emplantillado;
                $fecha = $orden->fecha_fin_emplantillado ?: $orden->fecha_fin_soladura;
            }

            $subtotal = $orden->total_pares * $precio;
            $totalGanado += $subtotal;

            return [
                'id' => $orden->id,
                'numeroOrden' => $orden->numero_orden,
                'referencia' => $orden->referencia,
                'pares' => $orden->total_pares,
                'precio' => $precio,
                'subtotal' => $subtotal,
                'fecha' => optional($fecha)->toISOString(),
            ];
        })->values();

        return response()->json([
            'totalGanado' => $totalGanado,
            'detalle' => $detalle,
        ]);
    }

    private function resolverPrecios(string $categoria, array $data): array
    {
        if ($categoria === 'ESPECIAL') {
            return [
                'corte' => (int) ($data['precioManualCorte'] ?? 0),
                'armado' => (int) ($data['precioManualArmado'] ?? 0),
                'costura' => (int) ($data['precioManualCostura'] ?? 0),
                'soladura' => (int) ($data['precioManualSoladura'] ?? 0),
                'emplantillado' => (int) ($data['precioManualEmplantillado'] ?? 0),
            ];
        }

        $tarifa = TarifaCategoria::where('nombre', $categoria)->first();

        return [
            'corte' => $tarifa?->precio_corte ?? 0,
            'armado' => $tarifa?->precio_armado ?? 0,
            'costura' => $tarifa?->precio_costura ?? 0,
            'soladura' => $tarifa?->precio_soladura ?? 0,
            'emplantillado' => $tarifa?->precio_emplantillado ?? 0,
        ];
    }

    private function debeEsperarIngresoStock(OrdenProduccion $orden): bool
    {
        return strtoupper((string) $orden->destino) === 'STOCK' && $orden->cliente_id === null;
    }

    private function inferirTipoProducto(string $referencia): string
    {
        $referencia = strtoupper(trim($referencia));

        if (
            str_starts_with($referencia, 'Z') ||
            str_starts_with($referencia, 'LOLAS') ||
            str_starts_with($referencia, 'LOLA') ||
            str_contains($referencia, 'TENIS') ||
            str_starts_with($referencia, 'P')
        ) {
            return 'PLATAFORMA';
        }

        return 'PLANA';
    }

    private function sumarOrdenAInventario(OrdenProduccion $orden, string $sucursal, string $tipo): void
    {
        $inventario = InventarioZapato::query()->firstOrNew([
            'referencia' => $orden->referencia,
            'color' => $orden->color,
            'sucursal' => $sucursal,
        ]);

        $inventario->tipo = $tipo;

        foreach (range(35, 42) as $talla) {
            $campo = "t{$talla}";
            $inventario->{$campo} = (int) ($inventario->{$campo} ?? 0) + (int) ($orden->{$campo} ?? 0);
        }

        $inventario->total = array_sum(array_map(
            fn (int $talla) => (int) ($inventario->{"t{$talla}"} ?? 0),
            range(35, 42)
        ));
        $inventario->updated_at = now();
        $inventario->save();
    }

    private function registrarMovimientoIngresoOrden(OrdenProduccion $orden, string $sucursal, string $tipo): void
    {
        $movimientos = [];
        $ahora = now();

        foreach (range(35, 42) as $talla) {
            $cantidad = (int) ($orden->{"t{$talla}"} ?? 0);
            if ($cantidad <= 0) {
                continue;
            }

            $movimientos[] = [
                'tipo_movimiento' => 'IN',
                'orden_produccion_id' => $orden->id,
                'venta_id' => null,
                'referencia' => (string) $orden->referencia,
                'color' => (string) $orden->color,
                'tipo' => $tipo,
                'sucursal' => $sucursal,
                'talla' => $talla,
                'cantidad' => $cantidad,
                'usuario_id' => null,
                'created_at' => $ahora,
            ];
        }

        if ($movimientos !== []) {
            InventarioMovimiento::query()->insert($movimientos);
        }
    }

    private function formatOrden(OrdenProduccion $orden): array
    {
        return [
            'id' => $orden->id,
            'numeroOrden' => $orden->numero_orden,
            'referencia' => $orden->referencia,
            'color' => $orden->color,
            'categoria' => $orden->categoria,
            'precioCorte' => $orden->precio_corte,
            'precioArmado' => $orden->precio_armado,
            'precioCostura' => $orden->precio_costura,
            'precioSoladura' => $orden->precio_soladura,
            'precioEmplantillado' => $orden->precio_emplantillado,
            'materiales' => $orden->materiales,
            'observacion' => $orden->observacion,
            'destino' => $orden->destino,
            'clienteId' => $orden->cliente_id,
            'cortadorId' => $orden->cortador_id,
            'armadorId' => $orden->armador_id,
            'costureroId' => $orden->costurero_id,
            'soladorId' => $orden->solador_id,
            'emplantilladorId' => $orden->emplantillador_id,
            'fechaInicio' => optional($orden->fecha_inicio)->toISOString(),
            'fechaFinCorte' => optional($orden->fecha_fin_corte)->toISOString(),
            'fechaFinArmado' => optional($orden->fecha_fin_armado)->toISOString(),
            'fechaFinCostura' => optional($orden->fecha_fin_costura)->toISOString(),
            'fechaFinSoladura' => optional($orden->fecha_fin_soladura)->toISOString(),
            'fechaFinEmplantillado' => optional($orden->fecha_fin_emplantillado)->toISOString(),
            'fechaFinTerminado' => optional($orden->fecha_fin_terminado)->toISOString(),
            'estado' => $orden->estado,
            'puedePasarAStock' => $orden->estado === 'LISTO_PARA_STOCK' && $this->debeEsperarIngresoStock($orden),
            't34' => $orden->t34,
            't35' => $orden->t35,
            't36' => $orden->t36,
            't37' => $orden->t37,
            't38' => $orden->t38,
            't39' => $orden->t39,
            't40' => $orden->t40,
            't41' => $orden->t41,
            't42' => $orden->t42,
            't43' => $orden->t43,
            't44' => $orden->t44,
            'totalPares' => $orden->total_pares,
        ];
    }
}
