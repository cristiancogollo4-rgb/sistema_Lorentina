<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Venta;

class DebugJuanSeeder extends Seeder
{
    public function run(): void
    {
        $u = User::where('nombre', 'LIKE', '%Juan%')->first();
        if (!$u) {
            $this->command->error('No se encontró a Juan');
            return;
        }
        $this->command->info('Usuario: ' . $u->nombre . ' (ID: ' . $u->id . ')');
        $this->command->info('Rol: ' . $u->rol);
        $this->command->info('Ventas Totales: ' . Venta::where('vendedor_id', $u->id)->count());
        $this->command->info('Ventas Semana: ' . Venta::where('vendedor_id', $u->id)->where('fecha_venta', '>=', now()->startOfWeek())->count());
        
        $ventasRecientes = Venta::where('vendedor_id', $u->id)->orderByDesc('fecha_venta')->limit(5)->get();
        foreach($ventasRecientes as $v) {
            $this->command->line(" - Venta ID: {$v->id} | Total: {$v->total} | Fecha: {$v->fecha_venta}");
        }
    }
}
