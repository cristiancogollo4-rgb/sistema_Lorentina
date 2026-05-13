<?php

namespace Database\Seeders;

use App\Models\Cliente;
use App\Models\DetalleVenta;
use App\Models\InventarioZapato;
use App\Models\Producto;
use App\Models\User;
use App\Models\Venta;
use App\Support\ProductoCatalog;
use App\Support\ProductoSync;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class LorentinaExcelSeeder extends Seeder
{
    public function run(): void
    {
        $data = $this->loadSeedData();

        DB::transaction(function () use ($data): void {
            $vendedorId = User::query()
                ->where('username', 'cristian')
                ->value('id');

            if (! $vendedorId) {
                throw new RuntimeException('No existe el usuario cristian. Ejecuta UserSeeder antes de LorentinaExcelSeeder.');
            }

            $vendedores = $this->seedSalesUsers($data['sales_users'] ?? []);
            $this->seedInventory($data['stock'] ?? []);
            $this->seedProducts($data['products'] ?? []);
            $clientes = $this->seedClients($data['clients'] ?? [], $vendedores, (int) $vendedorId);
            $productos = $this->productIndex();
            $this->seedSales($data['sales'] ?? [], $clientes, $productos, $vendedores, (int) $vendedorId);
        });
    }

    /**
     * @return array<string, mixed>
     */
    private function loadSeedData(): array
    {
        $path = database_path('seeders/data/lorentina_excel_seed.json');

        if (! File::exists($path)) {
            throw new RuntimeException("No se encontro el archivo de datos para la semilla: {$path}");
        }

        $data = json_decode(File::get($path), true);

        if (! is_array($data)) {
            throw new RuntimeException('El JSON de la semilla Lorentina no es valido.');
        }

        return $data;
    }

    /**
     * @param array<int, array<string, mixed>> $records
     * @return array<string, int>
     */
    private function seedSalesUsers(array $records): array
    {
        $users = [];

        foreach ($records as $record) {
            $user = User::query()->updateOrCreate(
                ['username' => (string) $record['username']],
                [
                    'nombre' => (string) ($record['nombre'] ?? 'Vendedor'),
                    'apellido' => (string) ($record['apellido'] ?? 'Excel'),
                    'password' => Hash::make('1234'),
                    'rol' => (string) ($record['rol'] ?? 'VENTAS'),
                    'activo' => true,
                    'telefono' => '3000000000',
                    'cedula' => null,
                ]
            );

            $users[(string) $user->username] = (int) $user->id;
        }

        return $users;
    }

    /**
     * @param array<int, array<string, mixed>> $records
     */
    private function seedInventory(array $records): void
    {
        InventarioZapato::query()
            ->whereIn('sucursal', ['CABECERA', 'FABRICA', 'TOTAL'])
            ->delete();

        $now = Carbon::now();
        $rows = array_map(function (array $record) use ($now): array {
            $row = [
                'referencia' => (string) $record['referencia'],
                'color' => (string) $record['color'],
                'sucursal' => (string) $record['sucursal'],
                'tipo' => (string) ($record['tipo'] ?? 'PLANA'),
                'updated_at' => $now,
            ];

            $total = 0;
            foreach (range(35, 42) as $talla) {
                $campo = "t{$talla}";
                $row[$campo] = (int) ($record[$campo] ?? 0);
                $total += $row[$campo];
            }

            $row['total'] = $total;

            return $row;
        }, $records);

        foreach (array_chunk($rows, 250) as $chunk) {
            DB::table('inventario_zapatos')->insert($chunk);
        }
    }

    /**
     * @param array<int, array<string, mixed>> $records
     */
    private function seedProducts(array $records): void
    {
        foreach ($records as $record) {
            ProductoSync::upsertConPreciosBase($record + [
                'descripcion' => 'Producto sincronizado desde Excel',
            ]);
        }
    }

    /**
     * @param array<int, array<string, mixed>> $records
     * @param array<string, int> $vendedores
     * @return array<string, array{id:int, tipo_cliente:string}>
     */
    private function seedClients(array $records, array $vendedores, int $defaultVendedorId): array
    {
        $clientes = [];

        foreach ($records as $record) {
            $vendedorId = $vendedores[(string) ($record['vendedor_username'] ?? '')] ?? $defaultVendedorId;

            $cliente = Cliente::query()->updateOrCreate(
                ['nombre' => (string) $record['nombre']],
                [
                    'telefono' => $record['telefono'] ?? null,
                    'email' => $record['email'] ?? null,
                    'direccion' => $record['direccion'] ?? null,
                    'pais' => (string) ($record['pais'] ?? 'Colombia'),
                    'departamento' => $record['departamento'] ?? null,
                    'region_estado' => $record['region_estado'] ?? null,
                    'ciudad' => $record['ciudad'] ?? null,
                    'codigo_postal' => $record['codigo_postal'] ?? null,
                    'moneda_preferida' => (string) ($record['moneda_preferida'] ?? 'COP'),
                    'tipo_cliente' => (string) ($record['tipo_cliente'] ?? 'DETAL'),
                    'activo' => (bool) ($record['activo'] ?? true),
                    'vendedor_id' => $vendedorId,
                ]
            );

            $clientes[$this->normalizeKey((string) $cliente->nombre)] = [
                'id' => (int) $cliente->id,
                'tipo_cliente' => (string) $cliente->tipo_cliente,
            ];
        }

        return $clientes;
    }

    /**
     * @return array<string, int>
     */
    private function productIndex(): array
    {
        return Producto::query()
            ->get(['id', 'referencia', 'color', 'tipo'])
            ->mapWithKeys(fn (Producto $producto): array => [
                $this->productKey(
                    (string) $producto->referencia,
                    (string) $producto->color,
                    (string) $producto->tipo
                ) => (int) $producto->id,
            ])
            ->all();
    }

    /**
     * @param array<int, array<string, mixed>> $records
     * @param array<string, array{id:int, tipo_cliente:string}> $clientes
     * @param array<string, int> $productos
     * @param array<string, int> $vendedores
     */
    private function seedSales(array $records, array $clientes, array $productos, array $vendedores, int $defaultVendedorId): void
    {
        $ventaIds = DetalleVenta::query()
            ->where('numero_orden', 'like', 'XLS-CRISTIAN|%')
            ->pluck('venta_id')
            ->unique()
            ->values();

        if ($ventaIds->isNotEmpty()) {
            Venta::query()->whereIn('id', $ventaIds)->delete();
        }

        foreach ($records as $record) {
            $cliente = $clientes[$this->normalizeKey((string) $record['cliente_nombre'])] ?? null;
            $vendedorId = $vendedores[(string) ($record['vendedor_username'] ?? '')] ?? $defaultVendedorId;

            if (! $cliente) {
                continue;
            }

            $clienteId = $cliente['id'];
            $esMayorista = in_array(strtoupper($cliente['tipo_cliente']), ['MAYORISTA', 'MAYOR'], true);

            $venta = Venta::query()->create([
                'cliente_id' => $clienteId,
                'vendedor_id' => $vendedorId,
                'canal_venta' => (string) ($record['canal_venta'] ?? 'ONLINE'),
                'local_id' => null,
                'fecha_venta' => Carbon::parse((string) ($record['fecha_venta'] ?? '2026-01-01'))->addDays(rand(0, 110))->addHours(rand(0, 23)),
                'total' => (float) ($record['total'] ?? 0),
                'metodo_pago' => (string) ($record['metodo_pago'] ?? 'NO ESPECIFICADO'),
                'notas' => 'Venta importada de Excel - Origen: ' . ($record['source_key'] ?? 'Desconocido'),
            ]);

            foreach (($record['items'] ?? []) as $item) {
                $item = $this->resolverItemVenta($item, $esMayorista);
                $productKey = $this->productKey((string) $item['referencia'], (string) $item['color'], (string) ($item['tipo'] ?? 'PLANA'));
                $productoId = $productos[$productKey] ?? null;

                if (! $productoId && $esMayorista) {
                    $producto = $this->productoDesdeCatalogo($item);
                    $productoId = (int) $producto->id;
                    $productos[$productKey] = $productoId;
                }

                if (! $productoId) {
                    continue;
                }

                DetalleVenta::query()->create([
                    'venta_id' => $venta->id,
                    'producto_id' => $productoId,
                    'orden_produccion_id' => null,
                    'numero_orden' => (string) $record['source_key'],
                    'referencia' => (string) $item['referencia'],
                    'color' => (string) $item['color'],
                    'talla' => (int) $item['talla'],
                    'cantidad' => (int) $item['cantidad'],
                    'precio_unitario' => (float) $item['precio_unitario'],
                ]);
            }
        }
    }

    /**
     * @param array<string, mixed> $item
     * @return array<string, mixed>
     */
    private function resolverItemVenta(array $item, bool $esMayorista): array
    {
        if (! $esMayorista) {
            return $item;
        }

        $referencia = (string) ($item['referencia'] ?? '');
        $color = (string) ($item['color'] ?? '');
        $tipo = (string) ($item['tipo'] ?? 'PLANA');

        if (ProductoCatalog::isAllowed($referencia, $color, $tipo)) {
            return $item;
        }

        $replacement = ProductoCatalog::replacementFor($referencia, $color, $tipo);

        if (! $replacement) {
            return $item;
        }

        $item['referencia'] = (string) ($replacement['referencia'] ?? $referencia);
        $item['color'] = (string) ($replacement['color'] ?? $color);
        $item['tipo'] = (string) ($replacement['tipo'] ?? $tipo);

        return $item;
    }

    /**
     * @param array<string, mixed> $item
     */
    private function productoDesdeCatalogo(array $item): Producto
    {
        $catalogItem = ProductoCatalog::find(
            (string) ($item['referencia'] ?? ''),
            (string) ($item['color'] ?? ''),
            (string) ($item['tipo'] ?? 'PLANA')
        );

        return ProductoSync::upsertConPreciosBase(($catalogItem ?? $item) + [
            'referencia' => (string) ($item['referencia'] ?? ''),
            'color' => (string) ($item['color'] ?? ''),
            'tipo' => (string) ($item['tipo'] ?? 'PLANA'),
            'descripcion' => 'Producto autorizado desde catalogo Drive',
        ]);
    }

    private function productKey(string $referencia, string $color, string $tipo): string
    {
        return $this->normalizeKey("{$referencia}|{$color}|{$tipo}");
    }

    private function normalizeKey(string $value): string
    {
        $normalized = trim(preg_replace('/\s+/', ' ', $value) ?? $value);

        return function_exists('mb_strtoupper')
            ? mb_strtoupper($normalized, 'UTF-8')
            : strtoupper($normalized);
    }
}
