<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\DetalleVenta;
use App\Models\InventarioZapato;
use App\Models\InventarioMovimiento;
use App\Models\Local;
use App\Models\Producto;
use App\Models\User;
use App\Models\Venta;
use App\Support\ProductoCatalog;
use App\Support\ProductoCategoria;
use App\Support\ProductoPrecio;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class VentaController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $vendedorId = $request->query('vendedor_id');
        Log::info("VentaController@index - vendedor_id recibido: " . ($vendedorId ?? 'NULL'));

        $query = Venta::query();
        if ($vendedorId) {
            $query->where('ventas.vendedor_id', (int) $vendedorId);
        }

        $ventas = $query
            ->with(['cliente:id,nombre,tipo_cliente', 'vendedor:id,nombre', 'local:id,nombre', 'items'])
            ->orderByDesc('fecha_venta')
            ->orderByDesc('id')
            ->get();

        Log::info("VentaController@index - Ventas encontradas: " . $ventas->count());

        $result = $ventas->map(function (Venta $venta) {
                return [
                    'id' => $venta->id,
                    'fechaVenta' => optional($venta->fecha_venta)->toISOString(),
                    'cliente' => $venta->cliente?->nombre,
                    'tipoCliente' => $venta->cliente?->tipo_cliente,
                    'vendedor' => $venta->vendedor?->nombre,
                    'canalVenta' => $venta->canal_venta,
                    'local' => $venta->local?->nombre,
                    'metodoPago' => $venta->metodo_pago,
                    'titularCuenta' => $venta->titular_cuenta,
                    'notas' => $venta->notas,
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

        return response()->json($result);
    }

    public function catalogo(): JsonResponse
    {
        $sucursal = strtoupper((string) request()->query('sucursal', 'CABECERA'));
        $vendedorId = request()->query('vendedor_id');

        if (! in_array($sucursal, ['CABECERA', 'FABRICA'], true)) {
            $sucursal = 'CABECERA';
        }

        $clientes = Cliente::query()
            ->where('activo', true)
            ->when($vendedorId, fn ($q) => $q->where('vendedor_id', (int) $vendedorId))
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'tipo_cliente', 'pais', 'ciudad', 'departamento', 'region_estado', 'moneda_preferida']);

        $locales = Local::query()
            ->where('activo', true)
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'direccion']);

        $vendedores = User::query()
            ->whereIn('rol', ['VENDEDOR', 'ADMIN'])
            ->where('activo', true)
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'apellido', 'rol']);

        $stockDisponible = InventarioZapato::query()
            ->where('sucursal', $sucursal)
            ->where('total', '>', 0)
            ->orderBy('referencia')
            ->orderBy('color')
            ->get();

        $productos = $this->sincronizarYObtenerProductosDesdeStock($stockDisponible);
        $productosPorClave = $productos->keyBy(fn (Producto $producto) => $this->productoKey(
            (string) $producto->referencia,
            (string) $producto->color,
            (string) $producto->tipo
        ));
        $opciones = [];

        foreach ($stockDisponible as $registro) {
            $producto = $productosPorClave->get($this->productoKey(
                $registro->referencia,
                $registro->color,
                $registro->tipo
            ));

            if (! $producto) {
                continue;
            }

            foreach ($this->tallasParaTipo($registro->tipo) as $talla) {
                $disponibles = (int) ($registro->{"t{$talla}"} ?? 0);

                if ($disponibles <= 0) {
                    continue;
                }

                $opciones[] = [
                    'key' => $producto->id . '-' . $talla,
                    'productoId' => $producto->id,
                    'nombreModelo' => $producto->nombre_modelo,
                    'referencia' => $registro->referencia,
                    'color' => $registro->color,
                    'tipo' => $registro->tipo,
                    'sucursal' => $registro->sucursal,
                    'talla' => $talla,
                    'disponibles' => $disponibles,
                    'catalogoPermitidoMayorista' => ProductoCatalog::isAllowed(
                        (string) $registro->referencia,
                        (string) $registro->color,
                        (string) $registro->tipo
                    ),
                ];
            }
        }

        return response()->json([
            'clientes' => $clientes,
            'locales' => $locales,
            'vendedores' => $vendedores,
            'sucursalSeleccionada' => $sucursal,
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
            'sucursal' => ['required', 'in:CABECERA,FABRICA'],
            'metodo_pago' => ['required', 'string', 'max:80'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.producto_id' => ['required', 'integer', 'exists:productos,id'],
            'items.*.talla' => ['required', 'integer', 'between:34,44'],
            'items.*.cantidad' => ['required', 'integer', 'min:1'],
            'items.*.precio_unitario' => ['required', 'numeric', 'min:0'],
            'notas' => ['nullable', 'string'],
        ]);

        $cliente = Cliente::query()->findOrFail((int) $data['cliente_id']);
        $esMayorista = in_array(strtoupper((string) $cliente->tipo_cliente), ['MAYORISTA', 'MAYOR'], true);

        if ($esMayorista) {
            $data['items'] = $this->normalizarItemsMayoristas(
                $data['items'],
                (string) $data['sucursal']
            );
        }

        $productoIds = collect($data['items'])->pluck('producto_id')->unique()->values()->all();
        $productos = Producto::query()
            ->whereIn('id', $productoIds)
            ->get()
            ->keyBy('id');

        $stockPorProducto = $this->obtenerStockPorProducto($productoIds, (string) $data['sucursal']);
        $consumidoEnSolicitud = [];
        $total = 0;

        foreach ($data['items'] as $item) {
            /** @var Producto|null $producto */
            $producto = $productos->get($item['producto_id']);

            if (! $producto) {
                return response()->json(['error' => 'El producto seleccionado no existe.'], 422);
            }

            $enStock = (int) ($stockPorProducto[$producto->id][$item['talla']] ?? 0);
            $claveSolicitud = $producto->id . '-' . $item['talla'];
            $consumidoActual = (int) ($consumidoEnSolicitud[$claveSolicitud] ?? 0);
            $disponible = $enStock - $consumidoActual;

            if ($disponible < (int) $item['cantidad']) {
                return response()->json([
                    'error' => "No hay suficientes pares disponibles para {$producto->nombre_modelo} talla {$item['talla']}.",
                ], 422);
            }

            $consumidoEnSolicitud[$claveSolicitud] = $consumidoActual + (int) $item['cantidad'];
            $total += (float) $item['precio_unitario'] * (int) $item['cantidad'];
        }

        $venta = DB::transaction(function () use ($data, $productos, $total) {
            $venta = Venta::create([
                'cliente_id' => $data['cliente_id'],
                'vendedor_id' => $data['vendedor_id'],
                'canal_venta' => $data['canal_venta'],
                'local_id' => $data['canal_venta'] === 'LOCAL' ? $data['local_id'] : null,
                'metodo_pago' => $data['metodo_pago'],
                'titular_cuenta' => $data['titular_cuenta'] ?? null,
                'notas' => $data['notas'] ?? null,
                'total' => $total,
                'fecha_venta' => now(),
            ]);

            foreach ($data['items'] as $item) {
                /** @var Producto $producto */
                $producto = $productos[$item['producto_id']];

                DetalleVenta::create([
                    'venta_id' => $venta->id,
                    'producto_id' => $producto->id,
                    'orden_produccion_id' => null,
                    'numero_orden' => null,
                    'referencia' => $producto->referencia ?? $producto->nombre_modelo,
                    'color' => $producto->color,
                    'talla' => $item['talla'],
                    'cantidad' => $item['cantidad'],
                    'precio_unitario' => $item['precio_unitario'],
                ]);
            }

            $this->descontarInventarioVenta($data['items'], $productos, (string) $data['sucursal'], (int) $venta->id);

            return $venta->load(['cliente:id,nombre', 'local:id,nombre', 'items']);
        });

        return response()->json([
            'id' => $venta->id,
            'cliente' => $venta->cliente?->nombre,
            'canalVenta' => $venta->canal_venta,
            'local' => $venta->local?->nombre,
            'total' => (float) $venta->total,
            'totalPares' => (int) $venta->items->sum('cantidad'),
            'titular_cuenta' => $venta->titular_cuenta,
        ], 201);
    }

    /**
     * @param Collection<int, InventarioZapato> $stockDisponible
     * @return Collection<int, Producto>
     */
    private function sincronizarYObtenerProductosDesdeStock(Collection $stockDisponible): Collection
    {
        $productoIds = [];

        foreach ($stockDisponible as $registro) {
            $categoriaId = ProductoCategoria::idSugerido(
                (string) $registro->referencia,
                (string) $registro->tipo
            );
            $categoriaNombre = \App\Models\TarifaCategoria::query()->where('id', $categoriaId)->value('nombre');
            $precios = ProductoPrecio::para((string) $registro->tipo, $categoriaNombre);

            $producto = Producto::query()->updateOrCreate(
                [
                    'referencia' => $registro->referencia,
                    'color' => $registro->color,
                    'tipo' => $registro->tipo,
                ],
                [
                    'nombre_modelo' => trim($registro->referencia . ' - ' . $registro->color),
                    'descripcion' => "Producto sincronizado desde stock {$registro->tipo}",
                    'precio_detal' => $precios['detal'],
                    'precio_mayor' => $precios['mayor'],
                    'costo_produccion' => 0,
                    'tarifa_categoria_id' => $categoriaId,
                    'activo' => true,
                    'imagen' => ProductoCatalog::imageUrlFor(
                        (string) $registro->referencia,
                        (string) $registro->color,
                        (string) $registro->tipo
                    ),
                ]
            );

            $productoIds[] = $producto->id;
        }

        return Producto::query()->whereIn('id', array_values(array_unique($productoIds)))->get();
    }

    /**
     * @param array<int, array<string, mixed>> $items
     * @return array<int, array<string, mixed>>
     */
    private function normalizarItemsMayoristas(array $items, string $sucursal): array
    {
        $consumido = [];

        return array_map(function (array $item) use ($sucursal, &$consumido): array {
            $producto = Producto::query()->find((int) $item['producto_id']);

            if (
                $producto &&
                ProductoCatalog::isAllowed(
                    (string) $producto->referencia,
                    (string) $producto->color,
                    (string) $producto->tipo
                )
            ) {
                $this->registrarConsumoSimulado($consumido, $producto, (int) $item['talla'], (int) $item['cantidad']);

                return $item;
            }

            $reemplazo = $this->resolverReemplazoMayorista(
                $producto,
                $sucursal,
                (int) $item['talla'],
                (int) $item['cantidad'],
                $consumido
            );

            $item['producto_id'] = $reemplazo->id;
            $this->registrarConsumoSimulado($consumido, $reemplazo, (int) $item['talla'], (int) $item['cantidad']);

            return $item;
        }, $items);
    }

    /**
     * @param array<string, int> $consumido
     */
    private function resolverReemplazoMayorista(
        ?Producto $producto,
        string $sucursal,
        int $talla,
        int $cantidad,
        array $consumido
    ): Producto {
        $preferido = $producto
            ? ProductoCatalog::replacementFor(
                (string) $producto->referencia,
                (string) $producto->color,
                (string) $producto->tipo
            )
            : null;

        $candidatos = array_values(array_filter([
            $preferido,
            ...ProductoCatalog::all(),
        ]));
        $revisados = [];

        foreach ($candidatos as $item) {
            $key = ProductoCatalog::itemKey($item);
            if (isset($revisados[$key])) {
                continue;
            }
            $revisados[$key] = true;

            $inventario = InventarioZapato::query()
                ->where('referencia', (string) ($item['referencia'] ?? ''))
                ->where('color', (string) ($item['color'] ?? ''))
                ->where('tipo', (string) ($item['tipo'] ?? 'PLANA'))
                ->where('sucursal', $sucursal)
                ->first();

            $campo = "t{$talla}";
            $disponible = (int) ($inventario?->{$campo} ?? 0);
            $yaConsumido = (int) ($consumido[$key . "|{$talla}"] ?? 0);

            if ($disponible - $yaConsumido < $cantidad) {
                continue;
            }

            return $this->productoDesdeCatalogo($item);
        }

        throw ValidationException::withMessages([
            'producto_id' => 'No hay un producto del catalogo autorizado con stock suficiente para reemplazar este item mayorista.',
        ]);
    }

    /**
     * @param array<string, mixed> $item
     */
    private function productoDesdeCatalogo(array $item): Producto
    {
        $categoriaId = ProductoCategoria::idSugerido(
            (string) ($item['referencia'] ?? ''),
            (string) ($item['tipo'] ?? 'PLANA')
        );
        $categoriaNombre = \App\Models\TarifaCategoria::query()->where('id', $categoriaId)->value('nombre');
        $precios = ProductoPrecio::para((string) ($item['tipo'] ?? 'PLANA'), $categoriaNombre);

        return Producto::query()->updateOrCreate(
            [
                'referencia' => (string) ($item['referencia'] ?? ''),
                'color' => (string) ($item['color'] ?? ''),
                'tipo' => (string) ($item['tipo'] ?? 'PLANA'),
            ],
            [
                'nombre_modelo' => (string) ($item['product'] ?? trim(($item['referencia'] ?? '') . ' - ' . ($item['color'] ?? ''))),
                'descripcion' => 'Producto autorizado desde catalogo Drive',
                'precio_detal' => $precios['detal'],
                'precio_mayor' => $precios['mayor'],
                'costo_produccion' => 0,
                'tarifa_categoria_id' => $categoriaId,
                'activo' => true,
                'imagen' => $item['image_url'] ?? null,
            ]
        );
    }

    /**
     * @param array<string, int> $consumido
     */
    private function registrarConsumoSimulado(array &$consumido, Producto $producto, int $talla, int $cantidad): void
    {
        $key = ProductoCatalog::productKey(
            (string) $producto->referencia,
            (string) $producto->color,
            (string) $producto->tipo
        ) . "|{$talla}";

        $consumido[$key] = (int) ($consumido[$key] ?? 0) + $cantidad;
    }

    /**
     * @param array<int, int> $productoIds
     * @return array<int, array<int, int>>
     */
    private function obtenerStockPorProducto(array $productoIds, string $sucursal): array
    {
        if ($productoIds === []) {
            return [];
        }

        $productos = Producto::query()
            ->whereIn('id', $productoIds)
            ->get(['id', 'referencia', 'color', 'tipo']);

        $stock = [];

        foreach ($productos as $producto) {
            $registro = InventarioZapato::query()
                ->where('sucursal', $sucursal)
                ->where('referencia', $producto->referencia)
                ->where('color', $producto->color)
                ->where('tipo', $producto->tipo)
                ->first();

            if (! $registro) {
                continue;
            }

            foreach ($this->tallasParaTipo((string) $producto->tipo) as $talla) {
                $stock[$producto->id][$talla] = (int) ($registro->{"t{$talla}"} ?? 0);
            }
        }

        return $stock;
    }

    /**
     * @param array<int, array<string, mixed>> $items
     * @param \Illuminate\Support\Collection<int, Producto> $productos
     */
    private function descontarInventarioVenta(array $items, Collection $productos, string $sucursal, int $ventaId): void
    {
        $totalesPorProducto = [];

        foreach ($items as $item) {
            /** @var Producto $producto */
            $producto = $productos[$item['producto_id']];
            $clave = $this->productoKey(
                (string) $producto->referencia,
                (string) $producto->color,
                (string) $producto->tipo
            );

            if (! isset($totalesPorProducto[$clave])) {
                $totalesPorProducto[$clave] = [
                    'producto' => $producto,
                    'tallas' => [],
                ];
            }

            $talla = (int) $item['talla'];
            $totalesPorProducto[$clave]['tallas'][$talla] = (int) ($totalesPorProducto[$clave]['tallas'][$talla] ?? 0) + (int) $item['cantidad'];
        }

        foreach ($totalesPorProducto as $registro) {
            /** @var Producto $producto */
            $producto = $registro['producto'];
            $inventarioSucursal = InventarioZapato::query()
                ->where('referencia', $producto->referencia)
                ->where('color', $producto->color)
                ->where('tipo', $producto->tipo)
                ->where('sucursal', $sucursal)
                ->lockForUpdate()
                ->first();

            if (! $inventarioSucursal) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'stock' => "No existe inventario en {$sucursal} para {$producto->nombre_modelo}.",
                ]);
            }

            $movimientos = [];
            $ahora = now();
            foreach ($registro['tallas'] as $talla => $cantidad) {
                $campo = "t{$talla}";
                if ((int) ($inventarioSucursal->{$campo} ?? 0) < $cantidad) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'stock' => "Stock insuficiente en {$sucursal} para {$producto->nombre_modelo} talla {$talla}.",
                    ]);
                }
                $inventarioSucursal->{$campo} = (int) ($inventarioSucursal->{$campo} ?? 0) - $cantidad;

                $movimientos[] = [
                    'tipo_movimiento' => 'OUT',
                    'orden_produccion_id' => null,
                    'venta_id' => $ventaId,
                    'referencia' => (string) $producto->referencia,
                    'color' => (string) $producto->color,
                    'tipo' => (string) $producto->tipo,
                    'sucursal' => $sucursal,
                    'talla' => (int) $talla,
                    'cantidad' => (int) $cantidad,
                    'usuario_id' => null,
                    'created_at' => $ahora,
                ];
            }

            if ($movimientos !== []) {
                InventarioMovimiento::query()->insert($movimientos);
            }

            $inventarioSucursal->total = $this->sumarTotalInventario($inventarioSucursal);
            $inventarioSucursal->updated_at = now();
            $inventarioSucursal->save();

            $this->recalcularTotalProducto(
                (string) $producto->referencia,
                (string) $producto->color,
                (string) $producto->tipo
            );
        }
    }

    /**
     * @param array<int, int> $productoIds
     * @return array<int, array<int, int>>
     */
    private function obtenerCantidadesVendidas(array $productoIds): array
    {
        if ($productoIds === []) {
            return [];
        }

        $vendidos = DB::table('detalle_ventas')
            ->select('producto_id', 'talla', DB::raw('SUM(cantidad) as cantidad_vendida'))
            ->whereIn('producto_id', $productoIds)
            ->groupBy('producto_id', 'talla')
            ->get();

        $resultado = [];

        foreach ($vendidos as $vendido) {
            $resultado[$vendido->producto_id][$vendido->talla] = (int) $vendido->cantidad_vendida;
        }

        return $resultado;
    }

    /**
     * @return array<int, int>
     */
    private function tallasParaTipo(string $tipo): array
    {
        return strtoupper($tipo) === 'PLATAFORMA'
            ? range(34, 42)
            : range(35, 42);
    }

    private function productoKey(string $referencia, string $color, string $tipo): string
    {
        return strtoupper(trim($referencia) . '|' . trim($color) . '|' . trim($tipo));
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
