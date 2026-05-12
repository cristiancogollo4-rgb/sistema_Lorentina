<?php

namespace Database\Seeders;

use App\Models\Cliente;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CompleteExistingClientsSeeder extends Seeder
{
    public function run(): void
    {
        $ubicaciones = [
            ['ciudad' => 'Bogota', 'departamento' => 'Cundinamarca', 'postal' => '110111'],
            ['ciudad' => 'Medellin', 'departamento' => 'Antioquia', 'postal' => '050021'],
            ['ciudad' => 'Cali', 'departamento' => 'Valle del Cauca', 'postal' => '760001'],
            ['ciudad' => 'Barranquilla', 'departamento' => 'Atlantico', 'postal' => '080001'],
            ['ciudad' => 'Bucaramanga', 'departamento' => 'Santander', 'postal' => '680001'],
            ['ciudad' => 'Cucuta', 'departamento' => 'Norte de Santander', 'postal' => '540001'],
            ['ciudad' => 'Cartagena', 'departamento' => 'Bolivar', 'postal' => '130001'],
            ['ciudad' => 'Pereira', 'departamento' => 'Risaralda', 'postal' => '660001'],
            ['ciudad' => 'Manizales', 'departamento' => 'Caldas', 'postal' => '170001'],
            ['ciudad' => 'Armenia', 'departamento' => 'Quindio', 'postal' => '630001'],
            ['ciudad' => 'Neiva', 'departamento' => 'Huila', 'postal' => '410001'],
            ['ciudad' => 'Monteria', 'departamento' => 'Cordoba', 'postal' => '230001'],
        ];
        $ubicacionesPorCiudad = collect($ubicaciones)->keyBy(fn (array $ubicacion): string => $this->normalizarCiudad($ubicacion['ciudad']));
        $postalesGenerados = collect($ubicaciones)->pluck('postal')->all();

        $clientes = Cliente::all();

        foreach ($clientes as $cliente) {
            $updated = false;
            $ubicacion = $ubicaciones[((int) $cliente->id - 1) % count($ubicaciones)];
            $ubicacionActual = $cliente->ciudad
                ? $ubicacionesPorCiudad->get($this->normalizarCiudad((string) $cliente->ciudad))
                : null;
            $numeroBase = 3000000000 + ((int) $cliente->id * 7919 % 99999999);
            $nombreCorreo = Str::slug(Str::ascii($cliente->nombre), '.');
            $dominio = $cliente->tipo_cliente === 'MAYORISTA' ? 'mayoristas.lorentina.test' : 'clientes.lorentina.test';

            if (!$cliente->telefono) {
                $cliente->telefono = (string) $numeroBase;
                $updated = true;
            }

            if (!$cliente->email) {
                $cliente->email = "{$nombreCorreo}.{$cliente->id}@{$dominio}";
                $updated = true;
            }

            if (!$cliente->direccion) {
                $cliente->direccion = 'Calle ' . (10 + ((int) $cliente->id % 80)) . ' # '
                    . (1 + ((int) $cliente->id % 60)) . '-'
                    . str_pad((string) (1 + ((int) $cliente->id % 98)), 2, '0', STR_PAD_LEFT);
                $updated = true;
            }

            if (!$cliente->ciudad) {
                $cliente->ciudad = $ubicacion['ciudad'];
                $cliente->departamento = $ubicacion['departamento'];
                $cliente->pais = 'Colombia';
                $updated = true;
            }

            if (!$cliente->departamento) {
                $cliente->departamento = $ubicacionActual['departamento'] ?? $ubicacion['departamento'];
                $updated = true;
            }

            if (!$cliente->pais) {
                $cliente->pais = 'Colombia';
                $updated = true;
            }

            if (!$cliente->codigo_postal) {
                $cliente->codigo_postal = $ubicacionActual['postal'] ?? $ubicacion['postal'];
                $updated = true;
            } elseif (
                $ubicacionActual
                && $cliente->codigo_postal !== $ubicacionActual['postal']
                && in_array($cliente->codigo_postal, $postalesGenerados, true)
            ) {
                $cliente->codigo_postal = $ubicacionActual['postal'];
                $updated = true;
            }

            if (!$cliente->moneda_preferida) {
                $cliente->moneda_preferida = 'COP';
                $updated = true;
            }

            if ($updated) {
                $cliente->save();
            }
        }
    }

    private function normalizarCiudad(string $ciudad): string
    {
        return Str::slug(Str::ascii($ciudad));
    }
}
