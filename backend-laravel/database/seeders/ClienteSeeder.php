<?php

namespace Database\Seeders;

use App\Models\Cliente;
use App\Models\User;
use Illuminate\Database\Seeder;

class ClienteSeeder extends Seeder
{
    public function run(): void
    {
        $vendedorId = User::query()
            ->where('username', 'cristian')
            ->value('id');

        $clientes = [
            [
                'nombre' => 'Calzado Rivera SAS',
                'telefono' => '3104567890',
                'email' => 'compras@calzadorivera.com',
                'direccion' => 'Cra 15 # 93-27',
                'pais' => 'Colombia',
                'departamento' => 'Cundinamarca',
                'region_estado' => null,
                'ciudad' => 'Bogota',
                'codigo_postal' => '110221',
                'moneda_preferida' => 'COP',
                'tipo_cliente' => 'MAYORISTA',
                'activo' => true,
                'vendedor_id' => $vendedorId,
            ],
            [
                'nombre' => 'Boutique Valentina',
                'telefono' => '3209876541',
                'email' => 'pedidos@boutiquevalentina.co',
                'direccion' => 'Calle 10 # 35-80',
                'pais' => 'Colombia',
                'departamento' => 'Antioquia',
                'region_estado' => null,
                'ciudad' => 'Medellin',
                'codigo_postal' => '050021',
                'moneda_preferida' => 'COP',
                'tipo_cliente' => 'MAYORISTA',
                'activo' => true,
                'vendedor_id' => $vendedorId,
            ],
            [
                'nombre' => 'Maria Fernanda Lopez',
                'telefono' => '3001234567',
                'email' => 'maria.lopez@gmail.com',
                'direccion' => 'Barrio La Playa',
                'pais' => 'Colombia',
                'departamento' => 'Atlantico',
                'region_estado' => null,
                'ciudad' => 'Barranquilla',
                'codigo_postal' => '080001',
                'moneda_preferida' => 'COP',
                'tipo_cliente' => 'DETAL',
                'activo' => true,
                'vendedor_id' => $vendedorId,
            ],
            [
                'nombre' => 'Distribuciones El Trebol',
                'telefono' => '3152223344',
                'email' => 'ventas@eltrebol.com',
                'direccion' => 'Av. Libertadores # 22-18',
                'pais' => 'Colombia',
                'departamento' => 'Santander',
                'region_estado' => null,
                'ciudad' => 'Bucaramanga',
                'codigo_postal' => '680001',
                'moneda_preferida' => 'COP',
                'tipo_cliente' => 'MAYORISTA',
                'activo' => true,
                'vendedor_id' => $vendedorId,
            ],
        ];

        foreach ($clientes as $cliente) {
            Cliente::updateOrCreate(
                ['email' => $cliente['email']],
                $cliente
            );
        }
    }
}
