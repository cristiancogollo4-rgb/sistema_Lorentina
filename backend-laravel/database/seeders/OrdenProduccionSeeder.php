<?php

namespace Database\Seeders;

use App\Models\Cliente;
use App\Models\DetalleVenta;
use App\Models\OrdenProduccion;
use App\Models\Producto;
use App\Models\TarifaCategoria;
use App\Models\User;
use App\Models\Venta;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class OrdenProduccionSeeder extends Seeder
{
    public function run(): void
    {
        $usuarios = User::query()
            ->whereIn('username', [
                'jorge.perez',
                'jackeline.rojas',
                'yolanda.diaz',
                'julian.martinez',
                'ricardo.sosa',
            ])
            ->pluck('id', 'username');

        $clientes = Cliente::query()
            ->whereIn('email', [
                'compras@calzadorivera.com',
                'pedidos@boutiquevalentina.co',
                'maria.lopez@gmail.com',
                'ventas@eltrebol.com',
            ])
            ->pluck('id', 'email');

        $tarifas = TarifaCategoria::query()
            ->get()
            ->keyBy('nombre');

        $productos = Producto::query()
            ->with('tarifaCategoria:id,nombre')
            ->where('activo', true)
            ->whereNotNull('referencia')
            ->whereNotNull('color')
            ->orderBy('referencia')
            ->orderBy('color')
            ->limit(4)
            ->get()
            ->values();

        if ($productos->count() < 4) {
            return;
        }

        $ordenes = [
            [
                'numero_orden' => 'OP-24001',
                'fecha_inicio' => Carbon::now()->subDays(4),
                'estado' => 'EN_CORTE',
                'referencia' => $productos[0]->referencia,
                'color' => $productos[0]->color,
                'materiales' => 'Cuero sintetico, suela TR, hebilla dorada',
                'observacion' => 'Pedido urgente para exhibicion',
                'categoria' => $productos[0]->tarifaCategoria?->nombre ?? 'ROMANA',
                'destino' => 'CLIENTE',
                'cliente_id' => $clientes['compras@calzadorivera.com'] ?? null,
                'cortador_id' => $usuarios['jorge.perez'] ?? null,
                'armador_id' => null,
                'costurero_id' => null,
                'solador_id' => null,
                'emplantillador_id' => null,
                't34' => 2,
                't35' => 4,
                't36' => 6,
                't37' => 8,
                't38' => 6,
                't39' => 4,
                't40' => 2,
                't41' => 0,
                't42' => 0,
                't43' => 0,
                't44' => 0,
            ],
            [
                'numero_orden' => 'OP-24002',
                'fecha_inicio' => Carbon::now()->subDays(6),
                'fecha_fin_corte' => Carbon::now()->subDays(5),
                'estado' => 'EN_ARMADO',
                'referencia' => $productos[1]->referencia,
                'color' => $productos[1]->color,
                'materiales' => 'Capellada microfibra, suela PVC, plantilla confort',
                'observacion' => 'Revisar horma 37 y 38',
                'categoria' => $productos[1]->tarifaCategoria?->nombre ?? 'CLASICA',
                'destino' => 'CLIENTE',
                'cliente_id' => $clientes['pedidos@boutiquevalentina.co'] ?? null,
                'cortador_id' => $usuarios['jorge.perez'] ?? null,
                'armador_id' => $usuarios['jackeline.rojas'] ?? null,
                'costurero_id' => null,
                'solador_id' => null,
                'emplantillador_id' => null,
                't34' => 0,
                't35' => 3,
                't36' => 5,
                't37' => 7,
                't38' => 7,
                't39' => 4,
                't40' => 2,
                't41' => 0,
                't42' => 0,
                't43' => 0,
                't44' => 0,
            ],
            [
                'numero_orden' => 'OP-24003',
                'fecha_inicio' => Carbon::now()->subDays(8),
                'fecha_fin_corte' => Carbon::now()->subDays(7),
                'fecha_fin_armado' => Carbon::now()->subDays(6),
                'fecha_fin_costura' => Carbon::now()->subDays(5),
                'estado' => 'EN_SOLADURA',
                'referencia' => $productos[2]->referencia,
                'color' => $productos[2]->color,
                'materiales' => 'Cuero vacuno, forro textil, suela expandida',
                'observacion' => 'Cliente solicita empaque individual',
                'categoria' => $productos[2]->tarifaCategoria?->nombre ?? 'PLATAFORMA / ZARA',
                'destino' => 'CLIENTE',
                'cliente_id' => $clientes['compras@calzadorivera.com'] ?? null,
                'cortador_id' => $usuarios['jorge.perez'] ?? null,
                'armador_id' => $usuarios['jackeline.rojas'] ?? null,
                'costurero_id' => $usuarios['yolanda.diaz'] ?? null,
                'solador_id' => $usuarios['julian.martinez'] ?? null,
                'emplantillador_id' => null,
                't34' => 0,
                't35' => 0,
                't36' => 4,
                't37' => 6,
                't38' => 8,
                't39' => 6,
                't40' => 4,
                't41' => 2,
                't42' => 0,
                't43' => 0,
                't44' => 0,
            ],
            [
                'numero_orden' => 'OP-24004',
                'fecha_inicio' => Carbon::now()->subDays(10),
                'fecha_fin_corte' => Carbon::now()->subDays(9),
                'fecha_fin_armado' => Carbon::now()->subDays(8),
                'fecha_fin_costura' => Carbon::now()->subDays(7),
                'fecha_fin_soladura' => Carbon::now()->subDays(6),
                'fecha_fin_emplantillado' => Carbon::now()->subDays(5),
                'fecha_fin_terminado' => Carbon::now()->subDays(5),
                'estado' => 'TERMINADO',
                'referencia' => $productos[3]->referencia,
                'color' => $productos[3]->color,
                'materiales' => 'Sintetico premium, suela EVA, plantilla memory foam',
                'observacion' => 'Lote listo para despacho',
                'categoria' => $productos[3]->tarifaCategoria?->nombre ?? 'CLASICA',
                'destino' => 'CLIENTE',
                'cliente_id' => $clientes['ventas@eltrebol.com'] ?? null,
                'cortador_id' => $usuarios['jorge.perez'] ?? null,
                'armador_id' => $usuarios['jackeline.rojas'] ?? null,
                'costurero_id' => $usuarios['yolanda.diaz'] ?? null,
                'solador_id' => $usuarios['julian.martinez'] ?? null,
                'emplantillador_id' => $usuarios['ricardo.sosa'] ?? null,
                't34' => 1,
                't35' => 2,
                't36' => 4,
                't37' => 6,
                't38' => 6,
                't39' => 5,
                't40' => 4,
                't41' => 2,
                't42' => 1,
                't43' => 0,
                't44' => 0,
                'precio_corte' => 2200,
                'precio_armado' => 1600,
                'precio_costura' => 1700,
                'precio_soladura' => 2600,
                'precio_emplantillado' => 700,
            ],
        ];

        foreach ($ordenes as $orden) {
            $categoria = $orden['categoria'];
            $tarifa = $tarifas->get($categoria);

            $tallas = [];
            $totalPares = 0;
            foreach (range(34, 44) as $talla) {
                $cantidad = (int) ($orden["t{$talla}"] ?? 0);
                $tallas["t{$talla}"] = $cantidad;
                $totalPares += $cantidad;
            }

            $ordenCreada = OrdenProduccion::updateOrCreate(
                ['numero_orden' => $orden['numero_orden']],
                array_merge($orden, $tallas, [
                    'precio_corte' => $orden['precio_corte'] ?? (int) ($tarifa?->precio_corte ?? 0),
                    'precio_armado' => $orden['precio_armado'] ?? (int) ($tarifa?->precio_armado ?? 0),
                    'precio_costura' => $orden['precio_costura'] ?? (int) ($tarifa?->precio_costura ?? 0),
                    'precio_soladura' => $orden['precio_soladura'] ?? (int) ($tarifa?->precio_soladura ?? 0),
                    'precio_emplantillado' => $orden['precio_emplantillado'] ?? (int) ($tarifa?->precio_emplantillado ?? 0),
                    'total_pares' => $totalPares,
                ])
            );

            if ($ordenCreada->cliente_id && strtoupper((string) $ordenCreada->destino) === 'CLIENTE') {
                $this->registrarVentaDesdeOrdenMayorista($ordenCreada);
            }
        }
    }

    private function registrarVentaDesdeOrdenMayorista(OrdenProduccion $orden): void
    {
        $nota = "Venta registrada automaticamente desde orden de fabricacion {$orden->numero_orden}.";
        $ventaIds = Venta::query()->where('notas', $nota)->pluck('id');

        if ($ventaIds->isNotEmpty()) {
            DetalleVenta::query()->whereIn('venta_id', $ventaIds)->delete();
            Venta::query()->whereIn('id', $ventaIds)->delete();
        }

        $producto = Producto::query()
            ->where('referencia', (string) $orden->referencia)
            ->where('color', (string) $orden->color)
            ->where('activo', true)
            ->orderBy('id')
            ->first();

        if (! $producto) {
            return;
        }

        $responsableId = User::query()
            ->where('rol', 'ADMIN')
            ->where('activo', true)
            ->orderBy('id')
            ->value('id');

        if (! $responsableId) {
            return;
        }

        $precioUnitario = (float) ($producto->precio_mayor ?: $producto->precio_detal ?: 0);
        $venta = Venta::query()->create([
            'cliente_id' => $orden->cliente_id,
            'vendedor_id' => $responsableId,
            'canal_venta' => 'FABRICA',
            'local_id' => null,
            'fecha_venta' => $orden->fecha_inicio,
            'total' => $precioUnitario * (int) $orden->total_pares,
            'metodo_pago' => 'PENDIENTE',
            'notas' => $nota,
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
}
