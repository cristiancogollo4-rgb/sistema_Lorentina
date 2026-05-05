<?php

namespace Database\Seeders;

use App\Models\Cliente;
use Illuminate\Database\Seeder;

class CompleteExistingClientsSeeder extends Seeder
{
    public function run(): void
    {
        $ciudades = [
            'Bogota' => 'Cundinamarca',
            'Medellin' => 'Antioquia',
            'Cali' => 'Valle del Cauca',
            'Barranquilla' => 'Atlantico',
            'Bucaramanga' => 'Santander',
            'Cucuta' => 'Norte de Santander',
            'Cartagena' => 'Bolivar',
            'Pereira' => 'Risaralda',
            'Manizales' => 'Caldas',
            'Armenia' => 'Quindio',
        ];

        $clientes = Cliente::all();

        foreach ($clientes as $cliente) {
            $updated = false;

            if (!$cliente->telefono) {
                $cliente->telefono = '31' . rand(0, 9) . rand(1000000, 9999999);
                $updated = true;
            }

            if (!$cliente->email) {
                $nombreLimpio = strtolower(str_replace(' ', '.', $cliente->nombre));
                $cliente->email = $nombreLimpio . '@example.com';
                $updated = true;
            }

            if (!$cliente->direccion) {
                $cliente->direccion = 'Calle ' . rand(1, 100) . ' # ' . rand(1, 50) . '-' . rand(1, 99);
                $updated = true;
            }

            if (!$cliente->ciudad) {
                $ciudad = array_rand($ciudades);
                $cliente->ciudad = $ciudad;
                $cliente->departamento = $ciudades[$ciudad];
                $cliente->pais = 'Colombia';
                $updated = true;
            }

            if (!$cliente->departamento && isset($ciudades[$cliente->ciudad])) {
                $cliente->departamento = $ciudades[$cliente->ciudad];
                $updated = true;
            }

            if (!$cliente->pais) {
                $cliente->pais = 'Colombia';
                $updated = true;
            }

            if ($updated) {
                $cliente->save();
            }
        }
    }
}
