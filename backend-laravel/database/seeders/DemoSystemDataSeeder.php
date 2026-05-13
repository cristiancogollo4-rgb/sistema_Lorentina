<?php

namespace Database\Seeders;

use App\Models\Cliente;
use App\Models\DetalleVenta;
use App\Models\OrdenProduccion;
use App\Models\Producto;
use App\Models\User;
use App\Models\Venta;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DemoSystemDataSeeder extends Seeder
{
    private const ORDEN_PREFIX = 'DEMO-OP-';
    private const VENTA_PREFIX = 'Venta demo sistema';

    public function run(): void
    {
        DB::transaction(function (): void {
            $this->limpiarDatosDemo();

            $productos = Producto::query()
                ->with('tarifaCategoria:id,nombre')
                ->where('activo', true)
                ->whereNotNull('referencia')
                ->whereNotNull('color')
                ->orderBy('id')
                ->limit(18)
                ->get();

            $clientes = Cliente::query()
                ->where('activo', true)
                ->orderBy('id')
                ->get()
                ->groupBy('tipo_cliente');

            if ($productos->isEmpty() || $clientes->isEmpty()) {
                return;
            }

            $usuarios = User::query()
                ->where('activo', true)
                ->get()
                ->groupBy('rol');

            $vendedores = $usuarios->get('VENDEDOR', collect())
                ->merge($usuarios->get('ADMIN', collect()))
                ->values();

            if ($vendedores->isEmpty()) {
                return;
            }

            $tarifas = DB::table('tarifa_categorias')->get()->keyBy('nombre');
            $ordenes = $this->crearOrdenesProduccion($productos, $clientes, $usuarios, $tarifas);
            $this->crearVentas($productos, $clientes, $vendedores, $ordenes);
        });
    }

    private function limpiarDatosDemo(): void
    {
        $ventaIds = Venta::query()
            ->where('notas', 'like', self::VENTA_PREFIX . '%')
            ->pluck('id');

        if ($ventaIds->isNotEmpty()) {
            DetalleVenta::query()->whereIn('venta_id', $ventaIds)->delete();
            Venta::query()->whereIn('id', $ventaIds)->delete();
        }

        OrdenProduccion::query()
            ->where('numero_orden', 'like', self::ORDEN_PREFIX . '%')
            ->delete();
    }

    private function crearOrdenesProduccion($productos, $clientes, $usuarios, $tarifas)
    {
        $estados = [
            'EN_CORTE',
            'EN_ARMADO',
            'EN_COSTURA',
            'EN_SOLADURA',
            'EN_EMPLANTILLADO',
            'TERMINADO',
            'TERMINADO',
            'EN_ARMADO',
        ];

        return collect($estados)->map(function (string $estado, int $index) use ($productos, $clientes, $usuarios, $tarifas): OrdenProduccion {
            $producto = $productos[$index % $productos->count()];
            $esCliente = $index % 3 !== 0;
            $mayoristas = $clientes->get('MAYORISTA', collect())->values();
            $cliente = $esCliente && $mayoristas->isNotEmpty()
                ? $mayoristas[$index % $mayoristas->count()]
                : null;
            $categoria = (string) ($producto->tarifaCategoria?->nombre ?? 'CLASICA');
            $tarifa = $tarifas->get($categoria);
            $fechaInicio = Carbon::now()->subDays(12 - $index);
            $tallas = $this->tallasParaOrden($index);
            $totalPares = array_sum($tallas);

            $orden = OrdenProduccion::query()->create(array_merge([
                'numero_orden' => self::ORDEN_PREFIX . str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT),
                'fecha_inicio' => $fechaInicio,
                'fecha_fin_corte' => $this->fechaEtapa($estado, $fechaInicio, 'CORTE'),
                'fecha_fin_armado' => $this->fechaEtapa($estado, $fechaInicio, 'ARMADO'),
                'fecha_fin_costura' => $this->fechaEtapa($estado, $fechaInicio, 'COSTURA'),
                'fecha_fin_soladura' => $this->fechaEtapa($estado, $fechaInicio, 'SOLADURA'),
                'fecha_fin_emplantillado' => $this->fechaEtapa($estado, $fechaInicio, 'EMPLANTILLADO'),
                'fecha_fin_terminado' => $estado === 'TERMINADO' ? $fechaInicio->copy()->addDays(6) : null,
                'estado' => $estado,
                'referencia' => (string) $producto->referencia,
                'color' => (string) $producto->color,
                'foto_url' => $producto->imagen,
                'materiales' => $this->materialesPorProducto((string) $producto->tipo),
                'observacion' => $esCliente
                    ? 'Orden demo vinculada a cliente existente para validar trazabilidad.'
                    : 'Orden demo para alimentar stock y revisar flujo interno.',
                'categoria' => $categoria,
                'precio_corte' => (int) ($tarifa->precio_corte ?? 2200),
                'precio_armado' => (int) ($tarifa->precio_armado ?? 1600),
                'precio_costura' => (int) ($tarifa->precio_costura ?? 1700),
                'precio_soladura' => (int) ($tarifa->precio_soladura ?? 2600),
                'precio_emplantillado' => (int) ($tarifa->precio_emplantillado ?? 700),
                'destino' => $cliente ? 'CLIENTE' : 'STOCK',
                'cliente_id' => $cliente?->id,
                'cortador_id' => $this->usuarioId($usuarios, 'CORTE', $index),
                'armador_id' => $this->usuarioId($usuarios, 'ARMADOR', $index),
                'costurero_id' => $this->usuarioId($usuarios, 'COSTURERO', $index),
                'solador_id' => $this->usuarioId($usuarios, 'SOLADOR', $index),
                'emplantillador_id' => $this->usuarioId($usuarios, 'EMPLANTILLADOR', $index),
                'total_pares' => $totalPares,
            ], $tallas));

            if ($orden->cliente_id) {
                $this->registrarVentaDesdeOrdenMayorista($orden, $usuarios, $index);
            }

            return $orden;
        });
    }

    private function crearVentas($productos, $clientes, $vendedores, $ordenes): void
    {
        $clientesVenta = $clientes->flatten(1)->values();
        $metodos = ['EFECTIVO', 'TRANSFERENCIA', 'NEQUI', 'TARJETA'];

        for ($i = 0; $i < 16; $i++) {
            $cliente = $clientesVenta[$i % $clientesVenta->count()];
            $vendedor = $vendedores[$i % $vendedores->count()];
            $esMayorista = strtoupper((string) $cliente->tipo_cliente) === 'MAYORISTA';
            $items = [];
            $total = 0;

            for ($j = 0; $j < ($esMayorista ? 3 : 2); $j++) {
                $producto = $productos[($i + $j) % $productos->count()];
                $cantidad = $esMayorista ? 6 + (($i + $j) % 5) : 1 + (($i + $j) % 2);
                $precio = $esMayorista
                    ? (float) ($producto->precio_mayor ?: $producto->precio_detal ?: 65000)
                    : (float) ($producto->precio_detal ?: $producto->precio_mayor ?: 85000);
                $subtotal = $cantidad * $precio;
                $orden = $ordenes->firstWhere('referencia', $producto->referencia);

                $items[] = [
                    'producto' => $producto,
                    'orden' => $orden,
                    'talla' => 35 + (($i + $j) % 8),
                    'cantidad' => $cantidad,
                    'precio_unitario' => $precio,
                ];
                $total += $subtotal;
            }

            $venta = Venta::query()->create([
                'cliente_id' => $cliente->id,
                'vendedor_id' => $vendedor->id,
                'canal_venta' => $i % 4 === 0 ? 'LOCAL' : 'ONLINE',
                'local_id' => null,
                'fecha_venta' => Carbon::now()->subDays(15 - $i)->setTime(9 + ($i % 8), 30),
                'total' => $total,
                'metodo_pago' => $metodos[$i % count($metodos)],
                'notas' => self::VENTA_PREFIX . ' #' . str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT),
            ]);

            foreach ($items as $item) {
                DetalleVenta::query()->create([
                    'venta_id' => $venta->id,
                    'producto_id' => $item['producto']->id,
                    'orden_produccion_id' => $item['orden']?->id,
                    'numero_orden' => $item['orden']?->numero_orden,
                    'referencia' => $item['producto']->referencia,
                    'color' => $item['producto']->color,
                    'talla' => $item['talla'],
                    'cantidad' => $item['cantidad'],
                    'precio_unitario' => $item['precio_unitario'],
                ]);
            }
        }
    }

    private function registrarVentaDesdeOrdenMayorista(OrdenProduccion $orden, $usuarios, int $index): void
    {
        $producto = Producto::query()
            ->where('referencia', $orden->referencia)
            ->where('color', $orden->color)
            ->orderByDesc('precio_mayor')
            ->first();

        if (! $producto) {
            return;
        }

        $vendedores = $usuarios->get('VENDEDOR', collect())->merge($usuarios->get('ADMIN', collect()))->values();
        $responsable = $index % 2 === 0
            ? ($vendedores->firstWhere('rol', 'ADMIN') ?? $vendedores->first())
            : $vendedores->firstWhere('rol', 'VENDEDOR');

        if (! $responsable) {
            return;
        }

        $precioUnitario = (float) ($producto->precio_mayor ?: $producto->precio_detal ?: 0);
        $venta = Venta::query()->create([
            'cliente_id' => $orden->cliente_id,
            'vendedor_id' => $responsable->id,
            'canal_venta' => 'FABRICA',
            'local_id' => null,
            'fecha_venta' => $orden->fecha_inicio,
            'total' => $precioUnitario * (int) $orden->total_pares,
            'metodo_pago' => 'PENDIENTE',
            'notas' => self::VENTA_PREFIX . " desde orden {$orden->numero_orden}",
        ]);

        foreach (range(34, 44) as $talla) {
            $cantidad = (int) ($orden->{"t{$talla}"} ?? 0);
            if ($cantidad <= 0) {
                continue;
            }

            DetalleVenta::query()->create([
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
    }

    private function tallasParaOrden(int $index): array
    {
        $tallas = [];

        foreach (range(34, 44) as $talla) {
            $tallas["t{$talla}"] = in_array($talla, range(35, 41), true)
                ? 1 + (($index + $talla) % 4)
                : 0;
        }

        return $tallas;
    }

    private function fechaEtapa(string $estado, Carbon $inicio, string $etapa): ?Carbon
    {
        $orden = ['CORTE', 'ARMADO', 'COSTURA', 'SOLADURA', 'EMPLANTILLADO'];
        $estadoEtapa = str_replace('EN_', '', $estado);

        if ($estado === 'TERMINADO') {
            return $inicio->copy()->addDays(array_search($etapa, $orden, true) + 1);
        }

        return array_search($etapa, $orden, true) < array_search($estadoEtapa, $orden, true)
            ? $inicio->copy()->addDays(array_search($etapa, $orden, true) + 1)
            : null;
    }

    private function usuarioId($usuarios, string $rol, int $index): ?int
    {
        $grupo = $usuarios->get($rol, collect())->values();

        return $grupo->isEmpty() ? null : (int) $grupo[$index % $grupo->count()]->id;
    }

    private function materialesPorProducto(string $tipo): string
    {
        return strtoupper($tipo) === 'PLATAFORMA'
            ? 'Capellada sintetica premium, plataforma liviana, plantilla confort.'
            : 'Capellada sintetica premium, suela TR, plantilla confort.';
    }
}
