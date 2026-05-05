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
            [
                'nombre' => 'Juan Camilo Restrepo',
                'telefono' => '3128889900',
                'email' => 'juan.restrepo@outlook.com',
                'direccion' => 'Calle 45 # 12-05',
                'pais' => 'Colombia',
                'departamento' => 'Valle del Cauca',
                'ciudad' => 'Cali',
                'tipo_cliente' => 'DETAL',
                'activo' => true,
                'vendedor_id' => $vendedorId,
            ],
            [
                'nombre' => 'Zapatos & Estilo Pereira',
                'telefono' => '3015554433',
                'email' => 'gerencia@zapatosestilo.com',
                'direccion' => 'Centro Comercial Victoria Local 204',
                'pais' => 'Colombia',
                'departamento' => 'Risaralda',
                'ciudad' => 'Pereira',
                'tipo_cliente' => 'MAYORISTA',
                'activo' => true,
                'vendedor_id' => $vendedorId,
            ],
            [
                'nombre' => 'Sandra Milena Gomez',
                'telefono' => '3187776655',
                'email' => 'sandra.gomez88@hotmail.com',
                'direccion' => 'Cra 23 # 45-12',
                'pais' => 'Colombia',
                'departamento' => 'Caldas',
                'ciudad' => 'Manizales',
                'tipo_cliente' => 'DETAL',
                'activo' => true,
                'vendedor_id' => $vendedorId,
            ],
            [
                'nombre' => 'Almacenes El Palacio del Calzado',
                'telefono' => '3109990011',
                'email' => 'compras@palaciocalzado.com',
                'direccion' => 'Calle 19 # 8-45 Piso 2',
                'pais' => 'Colombia',
                'departamento' => 'Quindio',
                'ciudad' => 'Armenia',
                'tipo_cliente' => 'MAYORISTA',
                'activo' => true,
                'vendedor_id' => $vendedorId,
            ],
            [
                'nombre' => 'Carlos Alberto Ruiz',
                'telefono' => '3214445566',
                'email' => 'carlos.ruiz.detal@gmail.com',
                'direccion' => 'Avenida Santander # 10-20',
                'pais' => 'Colombia',
                'departamento' => 'Bolivar',
                'ciudad' => 'Cartagena',
                'tipo_cliente' => 'DETAL',
                'activo' => true,
                'vendedor_id' => $vendedorId,
            ],
            [
                'nombre' => 'Moda y Confort Monteria',
                'telefono' => '3002221100',
                'email' => 'contacto@modaconfort.co',
                'direccion' => 'Calle 27 # 4-15',
                'pais' => 'Colombia',
                'departamento' => 'Cordoba',
                'ciudad' => 'Monteria',
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
