<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $password = Hash::make('1234');

        User::updateOrCreate(
            ['username' => 'cristian'],
            [
                'nombre' => 'Cristian',
                'apellido' => 'Administrador',
                'password' => $password,
                'rol' => 'ADMIN',
                'activo' => true,
                'telefono' => '3000000000',
                'cedula' => '111111111',
            ]
        );

        $empleados = [
            ['nombre' => 'Jorge', 'apellido' => 'Pérez', 'username' => 'jorge.perez', 'rol' => 'CORTE'],
            ['nombre' => 'Jhon', 'apellido' => 'Gómez', 'username' => 'jhon.gomez', 'rol' => 'CORTE'],
            ['nombre' => 'Jackeline', 'apellido' => 'Rojas', 'username' => 'jackeline.rojas', 'rol' => 'ARMADOR'],
            ['nombre' => 'Sandra', 'apellido' => 'Milena', 'username' => 'sandra.milena', 'rol' => 'ARMADOR'],
            ['nombre' => 'Ana', 'apellido' => 'Castellano', 'username' => 'ana.castellano', 'rol' => 'ARMADOR'],
            ['nombre' => 'Yolanda', 'apellido' => 'Díaz', 'username' => 'yolanda.diaz', 'rol' => 'COSTURERO'],
            ['nombre' => 'Andrea', 'apellido' => 'Ruiz', 'username' => 'andrea.ruiz', 'rol' => 'COSTURERO'],
            ['nombre' => 'Julian', 'apellido' => 'Martínez', 'username' => 'julian.martinez', 'rol' => 'SOLADOR'],
            ['nombre' => 'Cesar', 'apellido' => 'Romero', 'username' => 'cesar.romero', 'rol' => 'SOLADOR'],
            ['nombre' => 'Ricardo', 'apellido' => 'Sosa', 'username' => 'ricardo.sosa', 'rol' => 'EMPLANTILLADOR'],
        ];

        foreach ($empleados as $empleado) {
            User::updateOrCreate(
                ['username' => $empleado['username']],
                [
                    'nombre' => $empleado['nombre'],
                    'apellido' => $empleado['apellido'],
                    'password' => $password,
                    'rol' => $empleado['rol'],
                    'activo' => true,
                    'telefono' => '3000000000',
                    'cedula' => '123456789',
                ]
            );
        }

        // Vendedores
        User::updateOrCreate(
            ['username' => 'juan.vendedor'],
            [
                'nombre' => 'Juan',
                'apellido' => 'Vendedor',
                'password' => $password,
                'rol' => 'VENDEDOR',
                'activo' => true,
                'telefono' => '3210000000',
                'cedula' => '999999999',
            ]
        );
    }
}
