<?php

namespace App\Http\Controllers;

use App\Models\InventarioZapato;
use App\Models\Cliente;
use App\Models\DetalleVenta;
use App\Models\InventarioMovimiento;
use App\Models\NominaPago;
use App\Models\OrdenProduccion;
use App\Models\Producto;
use App\Models\TarifaCategoria;
use App\Models\User;
use App\Models\Venta;
use App\Support\ProductoCatalog;
use App\Support\ProductoPrecio;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


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

    public function catalogoProduccion(): JsonResponse
    {
        $productos = Producto::query()
                ->with('tarifaCategoria:id,nombre')
                ->where('activo', true)
                ->whereNotNull('referencia')
                ->whereNotNull('color')
            ->orderBy('referencia')
            ->orderBy('color')
            ->orderBy('tipo')
            ->get()
            ->map(fn (Producto $producto): array => $this->formatProductoProduccion($producto))
            ->values();

        return response()->json($productos);
    }

    public function crearProductoProduccion(Request $request): JsonResponse
    {
        $data = $request->validate([
            'referencia' => ['required', 'string', 'max:255'],
            'color' => ['required', 'string', 'max:255'],
            'tipo' => ['required', 'string', 'in:PLANA,PLATAFORMA'],
            'tarifaCategoriaId' => ['required', 'integer', 'exists:tarifa_categorias,id'],
            'nombreModelo' => ['nullable', 'string', 'max:255'],
            'precioDetal' => ['nullable', 'numeric', 'min:0'],
            'precioMayor' => ['nullable', 'numeric', 'min:0'],
            'costoProduccion' => ['nullable', 'numeric', 'min:0'],
        ]);

        $referencia = trim((string) $data['referencia']);
        $color = trim((string) $data['color']);
        $tipo = strtoupper(trim((string) $data['tipo']));
        $nombreModelo = trim((string) ($data['nombreModelo'] ?? ''));

        $producto = Producto::query()->firstOrNew([
                'referencia' => $referencia,
                'color' => $color,
                'tipo' => $tipo,
        ]);

        $producto->nombre_modelo = $nombreModelo !== '' ? $nombreModelo : ($producto->nombre_modelo ?: trim("{$referencia} {$color}"));
        $producto->descripcion = $producto->descripcion ?: 'Producto creado desde el modulo de fabricacion';
        $producto->tarifa_categoria_id = (int) $data['tarifaCategoriaId'];
        $categoriaNombre = TarifaCategoria::query()->where('id', (int) $data['tarifaCategoriaId'])->value('nombre');
        $preciosBase = ProductoPrecio::para($tipo, $categoriaNombre);
        $producto->precio_detal = array_key_exists('precioDetal', $data) && $data['precioDetal'] !== null ? (float) $data['precioDetal'] : $preciosBase['detal'];
        $producto->precio_mayor = array_key_exists('precioMayor', $data) && $data['precioMayor'] !== null ? (float) $data['precioMayor'] : $preciosBase['mayor'];
        $producto->costo_produccion = array_key_exists('costoProduccion', $data) && $data['costoProduccion'] !== null ? (float) $data['costoProduccion'] : (float) ($producto->costo_produccion ?? 0);
        $producto->activo = true;
        $producto->imagen = $producto->imagen ?: ProductoCatalog::imageUrlFor($referencia, $color, $tipo);
        $producto->save();

        return response()->json($this->formatProductoProduccion($producto), $producto->wasRecentlyCreated ? 201 : 200);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'productoId' => ['required', 'integer', 'exists:productos,id'],
            'categoria' => ['nullable', 'string'],
            'isEspecial' => ['nullable', 'boolean'],
            'materiales' => ['required', 'string'],
            'observacion' => ['nullable', 'string'],
            'destino' => ['required', 'string', 'in:STOCK,CLIENTE'],
            'clienteId' => ['nullable', 'integer', 'exists:clientes,id'],
            'vendedorId' => ['nullable', 'integer', 'exists:users,id'],
            'precioVentaUnitario' => ['nullable', 'numeric', 'min:0'],
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

        $isEspecial = filter_var($data['isEspecial'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $clienteId = null;
        $productoSeleccionado = Producto::query()->with('tarifaCategoria')->findOrFail((int) $data['productoId']);
        $productoSeleccionado = ProductoCatalog::applyToProduct($productoSeleccionado);

        if (! $productoSeleccionado->activo || empty($productoSeleccionado->referencia) || empty($productoSeleccionado->color) || ! $productoSeleccionado->tarifaCategoria) {
            return response()->json([
                'error' => 'Selecciona un producto activo con referencia, color y categoria para fabricar.',
            ], 422);
        }

        $categoria = (string) $productoSeleccionado->tarifaCategoria->nombre;
        $precios = $this->resolverPrecios($isEspecial, $categoria, $data);

        if ($data['destino'] === 'CLIENTE') {
            if (empty($data['clienteId'])) {
                return response()->json([
                    'error' => 'Selecciona un cliente mayorista para fabricar por pedido.',
                ], 422);
            }

            $cliente = Cliente::query()->findOrFail((int) $data['clienteId']);
            if (strtoupper((string) $cliente->tipo_cliente) !== 'MAYORISTA') {
                return response()->json([
                    'error' => 'Las ordenes por pedido cliente solo pueden asociarse a clientes mayoristas.',
                ], 422);
            }

            $clienteId = (int) $cliente->id;
        }

        $totalPares = 0;
        $tallas = [];
        foreach (range(34, 44) as $talla) {
            $valor = (int) ($data["t{$talla}"] ?? 0);
            $tallas["t{$talla}"] = $valor;
            $totalPares += $valor;
        }

        $venta = null;
        $orden = DB::transaction(function () use ($data, $categoria, $precios, $tallas, $totalPares, $clienteId, $productoSeleccionado, &$venta) {
            $orden = OrdenProduccion::create([
                'numero_orden' => 'OP-' . substr((string) round(microtime(true) * 1000), -6),
                'referencia' => $productoSeleccionado->referencia,
                'color' => $productoSeleccionado->color,
                'categoria' => $categoria,
                'precio_corte' => $precios['corte'],
                'precio_armado' => $precios['armado'],
                'precio_costura' => $precios['costura'],
                'precio_soladura' => $precios['soladura'],
                'precio_emplantillado' => $precios['emplantillado'],
                'materiales' => $data['materiales'],
                'observacion' => $data['observacion'] ?? null,
                'destino' => $data['destino'],
                'cliente_id' => $clienteId,
                'cortador_id' => $data['cortadorId'] ?? null,
                'total_pares' => $totalPares,
                'estado' => 'EN_CORTE',
                ...$tallas,
            ]);

            if ($clienteId !== null) {
                $venta = $this->registrarVentaDesdeOrdenMayorista(
                    $orden,
                    $data['vendedorId'] ?? null,
                    isset($data['precioVentaUnitario']) ? (float) $data['precioVentaUnitario'] : null,
                    $productoSeleccionado
                );
            }

            return $orden;
        });

        return response()->json([
            'msg' => 'Orden creada con precios desglosados',
            'orden' => $orden->numero_orden,
            'ventaId' => $venta?->id,
            'precioAplicado' => $precios['corte'],
        ]);
    }

    public function tablero(Request $request): JsonResponse
    {
        try {
            $vendedorId = $request->query('vendedor_id');
            $rango = $request->query('rango', 'produccion');
            $fechaInicio = $request->query('inicio');
            $fechaFin = $request->query('fin');
            $tipoFiltro = $request->query('tipo_filtro', 'activas');
            
            $queryOrdenes = OrdenProduccion::with('cliente:id,nombre');

            if ($tipoFiltro === 'activas') {
                $queryOrdenes->whereNotIn('estado', ['TERMINADO', 'EN_STOCK']);
            } elseif ($tipoFiltro === 'completadas') {
                $queryOrdenes->whereIn('estado', ['TERMINADO', 'EN_STOCK']);
            } elseif ($tipoFiltro === 'clientes') {
                $queryOrdenes->whereNotNull('cliente_id');
            } elseif ($tipoFiltro === 'stock') {
                $queryOrdenes->whereNull('cliente_id');
            }
            
            $qCreadas = OrdenProduccion::query();
            $qTerminadas = OrdenProduccion::query()->where('estado', 'EN_STOCK');

            if ($rango === 'semana') {
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
                $dInicio = Carbon::parse($fechaInicio)->startOfDay();
                $dFin = Carbon::parse($fechaFin)->endOfDay();
                $queryOrdenes->whereBetween('fecha_inicio', [$dInicio, $dFin]);
                $qCreadas->whereBetween('fecha_inicio', [$dInicio, $dFin]);
                $qTerminadas->whereBetween('fecha_fin_terminado', [$dInicio, $dFin]);
            }

            $ordenes = $queryOrdenes
                ->orderByRaw("
                    CASE
                        WHEN estado = 'EN_CORTE' AND cortador_id IS NULL THEN 0
                        WHEN estado = 'EN_ARMADO' AND armador_id IS NULL THEN 0
                        WHEN estado = 'EN_COSTURA' AND costurero_id IS NULL THEN 0
                        WHEN estado = 'EN_SOLADURA' AND solador_id IS NULL THEN 0
                        WHEN estado = 'EN_EMPLANTILLADO' AND emplantillador_id IS NULL THEN 0
                        ELSE 1
                    END
                ")
                ->orderByDesc('id')
                ->get()
                ->map(fn (OrdenProduccion $orden) => $this->formatOrden($orden));

            $empleados = User::query()
                ->where('rol', '!=', 'ADMIN')
                ->where('activo', true)
                ->orderBy('nombre')
                ->get(['id', 'nombre', 'apellido', 'rol']);

            $paresFabricar = (int) $qCreadas->sum('total_pares');
            $paresStock = (int) $qTerminadas->sum('total_pares');
            $pendientesAsignacion = $ordenes->filter(fn (array $orden) => (bool) $orden['pendienteAsignacion'])->count();
            $inicioSemana = now()->startOfWeek();
            $inicioMes = now()->startOfMonth();
            
            $ventasSemanaQuery = Venta::query()->where('fecha_venta', '>=', $inicioSemana);
            $ventasMesQuery = Venta::query()->where('fecha_venta', '>=', $inicioMes);
            
            if ($vendedorId) {
                $ventasSemanaQuery->where('vendedor_id', (int) $vendedorId);
                $ventasMesQuery->where('vendedor_id', (int) $vendedorId);
            }

            $ventasSemana = (float) $ventasSemanaQuery->sum('total');
            $ventasMes = (float) $ventasMesQuery->sum('total');

            $ventasVendedorBase = Venta::query();
            if ($vendedorId) {
                $ventasVendedorBase->where('vendedor_id', (int) $vendedorId);
            }

            $inicioSemanaAnterior = now()->startOfWeek()->subWeek();
            $finSemanaAnterior = now()->startOfWeek();

            // Optimización: Agrupar métricas de ventas en menos consultas
            $metricasVentas = (clone $ventasVendedorBase)
                ->selectRaw("
                    SUM(CASE WHEN fecha_venta >= ? THEN total ELSE 0 END) as ventas_semana,
                    SUM(CASE WHEN fecha_venta >= ? THEN total ELSE 0 END) as ventas_mes,
                    SUM(CASE WHEN fecha_venta >= ? AND fecha_venta < ? THEN total ELSE 0 END) as ventas_semana_anterior,
                    COUNT(CASE WHEN canal_venta = 'ONLINE' THEN 1 END) as ventas_sin_despachar,
                    COUNT(DISTINCT CASE WHEN canal_venta = 'ONLINE' THEN cliente_id END) as clientes_apartados
                ", [$inicioSemana, $inicioMes, $inicioSemanaAnterior, $finSemanaAnterior])
                ->first();

            Log::info("Métricas de Ventas detectadas:", [
                'vendedor_id' => $vendedorId,
                'semana' => $metricasVentas->ventas_semana ?? 0,
                'mes' => $metricasVentas->ventas_mes ?? 0
            ]);

            $ventasSemanaVendedor = (float) ($metricasVentas->ventas_semana ?? 0);
            $ventasMesVendedor = (float) ($metricasVentas->ventas_mes ?? 0);
            $ventasSemanaAnterior = (float) ($metricasVentas->ventas_semana_anterior ?? 0);
            $clientesConApartados = (int) ($metricasVentas->clientes_apartados ?? 0);
            $ventasSinDespachar = (int) ($metricasVentas->ventas_sin_despachar ?? 0);

            $caidaVentasSemana = $ventasSemanaAnterior > 0
                ? (($ventasSemanaVendedor - $ventasSemanaAnterior) / $ventasSemanaAnterior) * 100
                : 0;

            // Top Productos con Join optimizado
            $topProductos = [];
            try {
                $topProductos = DB::table('detalle_ventas')
                    ->join('ventas', 'detalle_ventas.venta_id', '=', 'ventas.id')
                    ->when($vendedorId, fn ($q) => $q->where('ventas.vendedor_id', (int) $vendedorId))
                    ->select('detalle_ventas.referencia', 'detalle_ventas.color', DB::raw('SUM(detalle_ventas.cantidad) as total_vendido'))
                    ->groupBy('detalle_ventas.referencia', 'detalle_ventas.color')
                    ->orderByDesc('total_vendido')
                    ->limit(5)
                    ->get();
            } catch (\Exception $e) {
                Log::warning("Fallo en topProductos: " . $e->getMessage());
            }

            // Baja rotación (Consulta simple a productos)
            $bajaRotacionAltoMargen = [];
            try {
                $bajaRotacionAltoMargen = DB::table('productos')
                    ->select('referencia', 'color', 'precio_detal', 'costo_produccion')
                    ->whereRaw('(precio_detal - costo_produccion) > 0')
                    ->orderByRaw('(precio_detal - costo_produccion) DESC')
                    ->limit(5)
                    ->get();
            } catch (\Exception $e) {
                Log::warning("Fallo en bajaRotacion: " . $e->getMessage());
            }

            // Alerta cliente importante (Una sola consulta)
            $alertaClienteImportante = null;
            try {
                $clienteTop = DB::table('ventas')
                    ->join('clientes', 'ventas.cliente_id', '=', 'clientes.id')
                    ->select('ventas.cliente_id', 'clientes.nombre', DB::raw('SUM(ventas.total) as total_acumulado'), DB::raw('MAX(ventas.fecha_venta) as ultima_fecha'))
                    ->when($vendedorId, fn ($q) => $q->where('ventas.vendedor_id', (int) $vendedorId))
                    ->groupBy('ventas.cliente_id', 'clientes.nombre')
                    ->orderByDesc('total_acumulado')
                    ->first();

                if ($clienteTop) {
                    $alertaClienteImportante = [
                        'clienteId' => $clienteTop->cliente_id,
                        'cliente' => $clienteTop->nombre,
                        'diasSinCompra' => $clienteTop->ultima_fecha ? now()->diffInDays(Carbon::parse($clienteTop->ultima_fecha)) : null,
                    ];
                }
            } catch (\Exception $e) {
                Log::warning("Fallo en alertaCliente: " . $e->getMessage());
            }

            return response()->json([
                'ordenes' => $ordenes,
                'empleados' => $empleados,
                'vendedores' => User::where('rol', 'VENDEDOR')->where('activo', true)->orderBy('nombre')->get(['id', 'nombre', 'apellido']),
                'stats' => [
                    'paresFabricar' => $paresFabricar,
                    'paresStock' => $paresStock,
                    'pendientesAsignacion' => $pendientesAsignacion,
                    'ventasSemana' => $ventasSemana,
                    'ventasMes' => $ventasMes,
                    'ventasSemanaVendedor' => $ventasSemanaVendedor,
                    'ventasMesVendedor' => $ventasMesVendedor,
                    'clientesConApartados' => $clientesConApartados,
                    'ventasSinDespachar' => $ventasSinDespachar,
                    'caidaVentasSemana' => $caidaVentasSemana,
                    'topProductos' => $topProductos,
                    'bajaRotacionAltoMargen' => $bajaRotacionAltoMargen,
                    'clienteImportanteSinCompra' => $alertaClienteImportante,
                ]
            ]);
        } catch (\Exception $e) {
            Log::error("Error crítico en tablero: " . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
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
            $orden->update(['estado' => 'EN_ARMADO', 'fecha_fin_corte' => $ahora, 'armador_id' => null]);
            $nuevoEstado = 'EN_ARMADO';
        } elseif (in_array($rol, ['ARMADOR', 'ARMADO'], true)) {
            $orden->update(['estado' => 'EN_COSTURA', 'fecha_fin_armado' => $ahora, 'costurero_id' => null]);
            $nuevoEstado = 'EN_COSTURA';
        } elseif (in_array($rol, ['COSTURERO', 'COSTURA'], true)) {
            $orden->update(['estado' => 'EN_SOLADURA', 'fecha_fin_costura' => $ahora, 'solador_id' => null]);
            $nuevoEstado = 'EN_SOLADURA';
        } elseif (in_array($rol, ['SOLADOR', 'SOLADURA'], true)) {
            $orden->update(['estado' => 'EN_EMPLANTILLADO', 'fecha_fin_soladura' => $ahora, 'emplantillador_id' => null]);
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
            $producto = $this->productoParaOrden($orden);
            $tipo = (string) $producto->tipo;

            $this->sumarOrdenAInventario($orden, 'FABRICA', $tipo);
            $this->sumarOrdenAInventario($orden, 'TOTAL', $tipo);
            $this->registrarMovimientoIngresoOrden($orden, 'FABRICA', $tipo);

            $orden->update(['estado' => 'EN_STOCK']);
        });

        return response()->json([
            'mensaje' => 'Orden ingresada a stock correctamente.',
            'nuevoEstado' => 'EN_STOCK',
        ]);
    }

    public function nomina(Request $request, int $empleadoId): JsonResponse
    {
        [$inicio, $fin] = $this->periodoNominaDesdeRequest($request);
        $empleado = User::find($empleadoId);

        if (! $empleado) {
            return response()->json(['error' => 'Empleado no encontrado.'], 404);
        }

        $nomina = $this->calcularNominaEmpleado($empleado, $inicio, $fin);
        $historial = NominaPago::query()
            ->where('empleado_id', $empleadoId)
            ->orderByDesc('periodo_fin')
            ->limit(12)
            ->get()
            ->map(fn (NominaPago $pago) => $this->formatPagoNomina($pago))
            ->values();

        return response()->json([
            ...$nomina,
            'periodo' => [
                'inicio' => $inicio->toDateString(),
                'fin' => $fin->toDateString(),
                'diaPago' => 'SABADO',
                'fechaPago' => $this->fechaPagoSabado($fin)->toDateString(),
            ],
            'historial' => $historial,
        ]);
    }

    public function nominaResumen(Request $request): JsonResponse
    {
        [$inicio, $fin] = $this->periodoNominaDesdeRequest($request);

        if ($fin->lessThan($inicio)) {
            return response()->json(['error' => 'La fecha final no puede ser anterior a la fecha inicial.'], 422);
        }

        $empleados = User::query()
            ->where('rol', '!=', 'ADMIN')
            ->where('activo', true)
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'apellido', 'rol']);

        $pagos = NominaPago::query()
            ->whereDate('periodo_inicio', $inicio->toDateString())
            ->whereDate('periodo_fin', $fin->toDateString())
            ->get()
            ->keyBy('empleado_id');

        $empleadosNomina = $empleados
            ->map(function (User $empleado) use ($inicio, $fin, $pagos) {
                $nomina = $this->calcularNominaEmpleado($empleado, $inicio, $fin);
                $pago = $pagos->get($empleado->id);

                return [
                    ...$nomina,
                    'pagado' => $pago !== null,
                    'pago' => $pago ? $this->formatPagoNomina($pago) : null,
                ];
            })
            ->values();

        return response()->json([
            'periodo' => [
                'inicio' => $inicio->toDateString(),
                'fin' => $fin->toDateString(),
                'diaPago' => 'SABADO',
            ],
            'reglas' => [
                'comisionDetal' => 5000,
                'comisionMayorista' => 2500,
            ],
            'totales' => [
                'empleados' => $empleadosNomina->count(),
                'pares' => (int) $empleadosNomina->sum('totalPares'),
                'pagar' => (int) $empleadosNomina->sum('totalGanado'),
                'pagado' => (int) $empleadosNomina->filter(fn ($empleado) => $empleado['pagado'])->sum('totalGanado'),
                'pendiente' => (int) $empleadosNomina->filter(fn ($empleado) => ! $empleado['pagado'])->sum('totalGanado'),
                'produccion' => (int) $empleadosNomina->filter(fn ($empleado) => $empleado['tipoNomina'] === 'PRODUCCION')->sum('totalGanado'),
                'ventas' => (int) $empleadosNomina->filter(fn ($empleado) => $empleado['tipoNomina'] === 'VENTAS')->sum('totalGanado'),
            ],
            'empleados' => $empleadosNomina,
        ]);
    }

    public function registrarPagoNomina(Request $request): JsonResponse
    {
        $data = $request->validate([
            'empleadoId' => ['required', 'integer', 'exists:users,id'],
            'inicio' => ['required', 'date'],
            'fin' => ['required', 'date'],
            'fechaPago' => ['nullable', 'date'],
            'notas' => ['nullable', 'string', 'max:1000'],
        ]);

        $inicio = Carbon::parse($data['inicio'])->startOfDay();
        $fin = Carbon::parse($data['fin'])->endOfDay();

        if ($fin->lessThan($inicio)) {
            return response()->json(['error' => 'La fecha final no puede ser anterior a la fecha inicial.'], 422);
        }

        $empleado = User::findOrFail((int) $data['empleadoId']);
        $nomina = $this->calcularNominaEmpleado($empleado, $inicio, $fin);

        $pago = NominaPago::updateOrCreate(
            [
                'empleado_id' => $empleado->id,
                'periodo_inicio' => $inicio->toDateString(),
                'periodo_fin' => $fin->toDateString(),
            ],
            [
                'fecha_pago' => isset($data['fechaPago'])
                    ? Carbon::parse($data['fechaPago'])->toDateString()
                    : $this->fechaPagoSabado($fin)->toDateString(),
                'estado' => 'PAGADO',
                'total_pares' => (int) $nomina['totalPares'],
                'total_tareas' => (int) ($nomina['totalTareas'] ?? $nomina['totalVentas'] ?? 0),
                'total_pagado' => (int) $nomina['totalGanado'],
                'detalle' => $nomina['detalle'],
                'notas' => $data['notas'] ?? null,
            ]
        );

        return response()->json([
            'mensaje' => 'Pago de nomina registrado correctamente.',
            'pago' => $this->formatPagoNomina($pago),
        ]);
    }

    private function rolProduccionConfig(string $rol): ?array
    {
        return match (strtoupper($rol)) {
            'CORTE' => [
                'label' => 'Corte',
                'empleado' => 'cortador_id',
                'fecha' => 'fecha_fin_corte',
                'precio' => 'precio_corte',
            ],
            'ARMADOR', 'ARMADO' => [
                'label' => 'Armado',
                'empleado' => 'armador_id',
                'fecha' => 'fecha_fin_armado',
                'precio' => 'precio_armado',
            ],
            'COSTURERO', 'COSTURA' => [
                'label' => 'Costura',
                'empleado' => 'costurero_id',
                'fecha' => 'fecha_fin_costura',
                'precio' => 'precio_costura',
            ],
            'SOLADOR', 'SOLADURA' => [
                'label' => 'Soladura',
                'empleado' => 'solador_id',
                'fecha' => 'fecha_fin_soladura',
                'precio' => 'precio_soladura',
            ],
            'EMPLANTILLADOR', 'EMPLANTILLADO' => [
                'label' => 'Emplantillado',
                'empleado' => 'emplantillador_id',
                'fecha' => 'fecha_fin_emplantillado',
                'precio' => 'precio_emplantillado',
            ],
            default => null,
        };
    }

    private function periodoNominaDesdeRequest(Request $request): array
    {
        $inicio = $request->query('inicio')
            ? Carbon::parse((string) $request->query('inicio'))->startOfDay()
            : now()->startOfWeek(Carbon::MONDAY)->startOfDay();
        $fin = $request->query('fin')
            ? Carbon::parse((string) $request->query('fin'))->endOfDay()
            : $inicio->copy()->addDays(5)->endOfDay();

        return [$inicio, $fin];
    }

    private function fechaPagoSabado(Carbon $fecha): Carbon
    {
        $pago = $fecha->copy()->startOfDay();
        while ($pago->dayOfWeek !== Carbon::SATURDAY) {
            $pago->addDay();
        }

        return $pago;
    }

    private function calcularNominaEmpleado(User $empleado, Carbon $inicio, Carbon $fin): array
    {
        $rol = strtoupper((string) $empleado->rol);
        $config = $this->rolProduccionConfig($rol);

        if ($config) {
            return $this->calcularNominaProduccion($empleado, $config, $inicio, $fin);
        }

        if ($rol === 'VENDEDOR') {
            return $this->calcularNominaVendedor($empleado, $inicio, $fin);
        }

        return [
            'empleadoId' => $empleado->id,
            'nombre' => trim($empleado->nombre . ' ' . $empleado->apellido),
            'rol' => $empleado->rol,
            'tipoNomina' => 'SIN_NOMINA',
            'totalTareas' => 0,
            'totalVentas' => 0,
            'totalPares' => 0,
            'totalGanado' => 0,
            'detalle' => [],
        ];
    }

    private function calcularNominaProduccion(User $empleado, array $config, Carbon $inicio, Carbon $fin): array
    {
        $ordenes = OrdenProduccion::query()
            ->where($config['empleado'], $empleado->id)
            ->whereBetween($config['fecha'], [$inicio, $fin])
            ->orderByDesc($config['fecha'])
            ->get();

        $totalPares = 0;
        $totalGanado = 0;
        $detalle = $ordenes->map(function (OrdenProduccion $orden) use ($config, &$totalPares, &$totalGanado) {
            $pares = (int) $orden->total_pares;
            $precio = (int) $orden->{$config['precio']};
            $subtotal = $pares * $precio;
            $totalPares += $pares;
            $totalGanado += $subtotal;

            return [
                'id' => $orden->id,
                'tipo' => 'PRODUCCION',
                'numeroOrden' => $orden->numero_orden,
                'referencia' => $orden->referencia,
                'color' => $orden->color,
                'categoria' => $orden->categoria,
                'tarea' => $config['label'],
                'pares' => $pares,
                'precio' => $precio,
                'valorUnitario' => $precio,
                'subtotal' => $subtotal,
                'fecha' => optional($orden->{$config['fecha']})->toISOString(),
            ];
        })->values();

        return [
            'empleadoId' => $empleado->id,
            'nombre' => trim($empleado->nombre . ' ' . $empleado->apellido),
            'rol' => $empleado->rol,
            'tipoNomina' => 'PRODUCCION',
            'totalTareas' => $detalle->count(),
            'totalVentas' => 0,
            'totalPares' => $totalPares,
            'totalGanado' => $totalGanado,
            'detalle' => $detalle,
        ];
    }

    private function calcularNominaVendedor(User $vendedor, Carbon $inicio, Carbon $fin): array
    {
        $ventas = Venta::query()
            ->with(['cliente:id,nombre,tipo_cliente', 'items'])
            ->where('vendedor_id', $vendedor->id)
            ->whereBetween('fecha_venta', [$inicio, $fin])
            ->orderByDesc('fecha_venta')
            ->get();

        $totalPares = 0;
        $totalComision = 0;
        $detalle = $ventas->map(function (Venta $venta) use (&$totalPares, &$totalComision) {
            $tipoCliente = strtoupper((string) ($venta->cliente?->tipo_cliente ?? 'DETAL'));
            $esMayorista = in_array($tipoCliente, ['MAYORISTA', 'MAYOR'], true);
            $valorUnitario = $esMayorista ? 2500 : 5000;
            $pares = (int) $venta->items->sum('cantidad');
            $subtotal = $pares * $valorUnitario;
            $totalPares += $pares;
            $totalComision += $subtotal;

            return [
                'id' => $venta->id,
                'tipo' => 'VENTA',
                'cliente' => $venta->cliente?->nombre,
                'tipoCliente' => $esMayorista ? 'MAYORISTA' : 'DETAL',
                'pares' => $pares,
                'precio' => $valorUnitario,
                'valorUnitario' => $valorUnitario,
                'subtotal' => $subtotal,
                'totalVenta' => (float) $venta->total,
                'fecha' => optional($venta->fecha_venta)->toISOString(),
            ];
        })->values();

        return [
            'empleadoId' => $vendedor->id,
            'nombre' => trim($vendedor->nombre . ' ' . $vendedor->apellido),
            'rol' => $vendedor->rol,
            'tipoNomina' => 'VENTAS',
            'totalTareas' => 0,
            'totalVentas' => $detalle->count(),
            'totalPares' => $totalPares,
            'totalGanado' => $totalComision,
            'detalle' => $detalle,
        ];
    }

    private function formatPagoNomina(NominaPago $pago): array
    {
        return [
            'id' => $pago->id,
            'empleadoId' => $pago->empleado_id,
            'periodoInicio' => optional($pago->periodo_inicio)->toDateString(),
            'periodoFin' => optional($pago->periodo_fin)->toDateString(),
            'fechaPago' => optional($pago->fecha_pago)->toDateString(),
            'estado' => $pago->estado,
            'totalPares' => $pago->total_pares,
            'totalTareas' => $pago->total_tareas,
            'totalPagado' => $pago->total_pagado,
            'detalle' => $pago->detalle ?? [],
            'notas' => $pago->notas,
        ];
    }

    private function resolverPrecios(bool $isEspecial, string $categoria, array $data): array
    {
        if ($isEspecial) {
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

    private function registrarVentaDesdeOrdenMayorista(OrdenProduccion $orden, ?int $vendedorId, ?float $precioVentaUnitario = null, ?Producto $productoBase = null): Venta
    {
        $producto = $productoBase ?? $this->productoParaOrden($orden);
        $precioUnitario = $precioVentaUnitario ?? (float) ($producto->precio_mayor ?: $producto->precio_detal ?: 0);
        $adminId = User::query()
            ->where('rol', 'ADMIN')
            ->where('activo', true)
            ->orderBy('id')
            ->value('id');

        $responsableId = $vendedorId ?: $adminId;
        if (! $responsableId) {
            throw new \RuntimeException('No hay un usuario administrador activo para registrar la venta de fabrica.');
        }

        $venta = Venta::create([
            'cliente_id' => $orden->cliente_id,
            'vendedor_id' => $responsableId,
            'canal_venta' => 'FABRICA',
            'local_id' => null,
            'metodo_pago' => 'PENDIENTE',
            'titular_cuenta' => null,
            'notas' => "Venta registrada automaticamente desde orden de fabricacion {$orden->numero_orden}.",
            'total' => $precioUnitario * (int) $orden->total_pares,
            'fecha_venta' => now(),
        ]);

        foreach (range(34, 44) as $talla) {
            $cantidad = (int) ($orden->{"t{$talla}"} ?? 0);
            if ($cantidad <= 0) {
                continue;
            }

            DetalleVenta::create([
                'venta_id' => $venta->id,
                'producto_id' => $producto->id,
                'orden_produccion_id' => $orden->id,
                'numero_orden' => $orden->numero_orden,
                'referencia' => $orden->referencia,
                'color' => $orden->color,
                'talla' => $talla,
                'cantidad' => $cantidad,
                'precio_unitario' => $precioUnitario,
            ]);
        }

        return $venta;
    }

    private function productoParaOrden(OrdenProduccion $orden): Producto
    {
        $producto = Producto::query()
            ->where('referencia', (string) $orden->referencia)
            ->where('color', (string) $orden->color)
            ->where('activo', true)
            ->orderBy('id')
            ->first();

        if (! $producto) {
            throw new \RuntimeException("La orden {$orden->numero_orden} usa una referencia/color que no existe en productos.");
        }

        return ProductoCatalog::applyToProduct($producto);
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
        [$rolActual, $responsableActualId] = $this->responsableActualOrden($orden);

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
            'clienteNombre' => $orden->cliente ? $orden->cliente->nombre : null,
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
            'rolActual' => $rolActual,
            'responsableActualId' => $responsableActualId,
            'pendienteAsignacion' => $rolActual !== null && $responsableActualId === null,
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

    private function formatProductoProduccion(Producto $producto): array
    {
        $producto->loadMissing('tarifaCategoria:id,nombre');
        $producto = ProductoCatalog::applyToProduct($producto);

        return [
            'id' => $producto->id,
            'nombreModelo' => $producto->nombre_modelo,
            'referencia' => $producto->referencia,
            'color' => $producto->color,
            'tipo' => $producto->tipo,
            'tarifaCategoriaId' => $producto->tarifa_categoria_id,
            'tarifaCategoriaNombre' => $producto->tarifaCategoria?->nombre,
            'precioDetal' => $producto->precio_detal,
            'precioMayor' => $producto->precio_mayor,
            'imagen' => $producto->imagen_src,
        ];
    }

    private function responsableActualOrden(OrdenProduccion $orden): array
    {
        return match ((string) $orden->estado) {
            'EN_CORTE' => ['CORTE', $orden->cortador_id],
            'EN_ARMADO' => ['ARMADOR', $orden->armador_id],
            'EN_COSTURA' => ['COSTURERO', $orden->costurero_id],
            'EN_SOLADURA' => ['SOLADOR', $orden->solador_id],
            'EN_EMPLANTILLADO' => ['EMPLANTILLADOR', $orden->emplantillador_id],
            default => [null, null],
        };
    }
}
