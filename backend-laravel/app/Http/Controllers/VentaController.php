<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\DetalleVenta;
use App\Models\Local;
use App\Models\OrdenProduccion;
use App\Models\Producto;
use App\Models\Venta;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VentaController extends Controller
{
    public function index(): JsonResponse
    {
        $ventas = Venta::query()
            ->with(['cliente:id,nombre,tipo_cliente', 'vendedor:id,nombre', 'local:id,nombre', 'items'])
            ->orderByDesc('fecha_venta')
            ->orderByDesc('id')
            ->get()
            ->map(function (Venta $venta) {
                return [
                    'id' => $venta->id,
                    'fechaVenta' => optional($venta->fecha_venta)->toISOString(),
                    'cliente' => $venta->cliente?->nombre,
                    'tipoCliente' => $venta->cliente?->tipo_cliente,
                    'vendedor' => $venta->vendedor?->nombre,
                    'canalVenta' => $venta->canal_venta,
                    'local' => $venta->local?->nombre,
                    'metodoPago' => $venta->metodo_pago,
                    'total' => (float) $venta->total,
                    'totalPares' => (int) $venta->items->sum('cantidad'),
                    'items' => $venta->items->map(function (DetalleVenta $item) {
                        return [
                            'id' => $item->id,
                            'numeroOrden' => $item->numero_orden,
                            'referencia' => $item->referencia,
                            'color' => $item->color,
                            'talla' => $item->talla,
                            'cantidad' => $item->cantidad,
                            'precioUnitario' => (float) $item->precio_unitario,
                            'subtotal' => (float) $item->cantidad * (float) $item->precio_unitario,
                        ];
                    })->values(),
                ];
            })
            ->values();

        return response()->json($ventas);
    }

    public function catalogo(): JsonResponse
    {
        $clientes = Cliente::query()
            ->where('activo', true)
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'tipo_cliente', 'pais', 'ciudad', 'departamento', 'region_estado', 'moneda_preferida']);

        $locales = Local::query()
            ->where('activo', true)
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'direccion']);

        $ordenesTerminadas = OrdenProduccion::query()
            ->where('estado', 'TERMINADO')
            ->orderByDesc('fecha_fin_terminado')
            ->orderByDesc('id')
            ->get();

        $vendidoPorOrdenYTalla = $this->obtenerCantidadesVendidas($ordenesTerminadas->pluck('id')->all());
        $opciones = [];

        foreach ($ordenesTerminadas as $orden) {
            foreach (range(34, 44) as $talla) {
                $fabricado = (int) ($orden->{"t{$talla}"} ?? 0);
                $vendido = (int) ($vendidoPorOrdenYTalla[$orden->id][$talla] ?? 0);
                $disponibles = $fabricado - $vendido;

                if ($disponibles <= 0) {
                    continue;
                }

                $opciones[] = [
                    'key' => $orden->id . '-' . $talla,
                    'ordenId' => $orden->id,
                    'numeroOrden' => $orden->numero_orden,
                    'referencia' => $orden->referencia,
                    'color' => $orden->color,
                    'destino' => $orden->destino,
                    'talla' => $talla,
                    'disponibles' => $disponibles,
                    'fechaTerminado' => optional($orden->fecha_fin_terminado)->toISOString(),
                ];
            }
        }

        return response()->json([
            'clientes' => $clientes,
            'locales' => $locales,
            'paresDisponibles' => $opciones,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'cliente_id' => ['required', 'integer', 'exists:clientes,id'],
            'vendedor_id' => ['required', 'integer', 'exists:users,id'],
            'canal_venta' => ['required', 'in:ONLINE,LOCAL'],
            'local_id' => ['nullable', 'integer', 'required_if:canal_venta,LOCAL', 'exists:locales,id'],
            'metodo_pago' => ['required', 'string', 'max:80'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.orden_produccion_id' => ['required', 'integer', 'exists:ordenes_produccion,id'],
            'items.*.talla' => ['required', 'integer', 'between:34,44'],
            'items.*.cantidad' => ['required', 'integer', 'min:1'],
            'items.*.precio_unitario' => ['required', 'numeric', 'min:0'],
        ]);

        $orderIds = collect($data['items'])->pluck('orden_produccion_id')->unique()->values()->all();
        $ordenes = OrdenProduccion::query()
            ->whereIn('id', $orderIds)
            ->get()
            ->keyBy('id');

        $vendidoPorOrdenYTalla = $this->obtenerCantidadesVendidas($orderIds);
        $consumidoEnSolicitud = [];
        $total = 0;

        foreach ($data['items'] as $item) {
            /** @var OrdenProduccion|null $orden */
            $orden = $ordenes->get($item['orden_produccion_id']);

            if (! $orden || $orden->estado !== 'TERMINADO') {
                return response()->json(['error' => 'Solo se pueden vender pares de tareas terminadas.'], 422);
            }

            $fabricado = (int) ($orden->{"t{$item['talla']}"} ?? 0);
            $vendido = (int) ($vendidoPorOrdenYTalla[$orden->id][$item['talla']] ?? 0);
            $claveSolicitud = $orden->id . '-' . $item['talla'];
            $consumidoActual = (int) ($consumidoEnSolicitud[$claveSolicitud] ?? 0);
            $disponible = $fabricado - $vendido - $consumidoActual;

            if ($disponible < (int) $item['cantidad']) {
                return response()->json([
                    'error' => "No hay suficientes pares disponibles para {$orden->numero_orden} talla {$item['talla']}.",
                ], 422);
            }

            $consumidoEnSolicitud[$claveSolicitud] = $consumidoActual + (int) $item['cantidad'];
            $total += (float) $item['precio_unitario'] * (int) $item['cantidad'];
        }

        $venta = DB::transaction(function () use ($data, $ordenes, $total) {
            $venta = Venta::create([
                'cliente_id' => $data['cliente_id'],
                'vendedor_id' => $data['vendedor_id'],
                'canal_venta' => $data['canal_venta'],
                'local_id' => $data['canal_venta'] === 'LOCAL' ? $data['local_id'] : null,
                'metodo_pago' => $data['metodo_pago'],
                'total' => $total,
                'fecha_venta' => now(),
            ]);

            foreach ($data['items'] as $item) {
                /** @var OrdenProduccion $orden */
                $orden = $ordenes[$item['orden_produccion_id']];
                $producto = $this->resolverProductoDesdeOrden($orden);

                DetalleVenta::create([
                    'venta_id' => $venta->id,
                    'producto_id' => $producto->id,
                    'orden_produccion_id' => $orden->id,
                    'numero_orden' => $orden->numero_orden,
                    'referencia' => $orden->referencia,
                    'color' => $orden->color,
                    'talla' => $item['talla'],
                    'cantidad' => $item['cantidad'],
                    'precio_unitario' => $item['precio_unitario'],
                ]);
            }

            return $venta->load(['cliente:id,nombre', 'local:id,nombre', 'items']);
        });

        return response()->json([
            'id' => $venta->id,
            'cliente' => $venta->cliente?->nombre,
            'canalVenta' => $venta->canal_venta,
            'local' => $venta->local?->nombre,
            'total' => (float) $venta->total,
            'totalPares' => (int) $venta->items->sum('cantidad'),
        ], 201);
    }

    private function resolverProductoDesdeOrden(OrdenProduccion $orden): Producto
    {
        $nombreModelo = trim($orden->referencia . ' - ' . $orden->color);

        return Producto::query()->firstOrCreate(
            ['nombre_modelo' => $nombreModelo],
            [
                'descripcion' => 'Generado automaticamente desde la orden ' . $orden->numero_orden,
                'precio_detal' => 0,
                'precio_mayor' => 0,
                'costo_produccion' => 0,
                'activo' => true,
                'created_at' => now(),
            ]
        );
    }

    private function obtenerCantidadesVendidas(array $orderIds): array
    {
        if ($orderIds === []) {
            return [];
        }

        $vendidos = DB::table('detalle_ventas')
            ->select('orden_produccion_id', 'talla', DB::raw('SUM(cantidad) as cantidad_vendida'))
            ->whereIn('orden_produccion_id', $orderIds)
            ->groupBy('orden_produccion_id', 'talla')
            ->get();

        $resultado = [];

        foreach ($vendidos as $vendido) {
            $resultado[$vendido->orden_produccion_id][$vendido->talla] = (int) $vendido->cantidad_vendida;
        }

        return $resultado;
    }
}
