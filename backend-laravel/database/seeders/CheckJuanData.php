<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Cliente;
use App\Models\Venta;

class CheckJuanData extends Seeder
{
    public function run(): void
    {
        $juan = User::where('nombre', 'LIKE', '%Juan%')->first();
        if (!$juan) {
            $this->command->error("Juan no existe");
            return;
        }

        $this->command->info("Juan ID: " . $juan->id);
        $this->command->info("Clientes: " . Cliente::where('vendedor_id', $juan->id)->count());
        $this->command->info("Clientes Mayoristas: " . Cliente::where('vendedor_id', $juan->id)->where('tipo_cliente', 'MAYORISTA')->count());
        $this->command->info("Ventas: " . Venta::where('vendedor_id', $juan->id)->count());
        
        $ventasMayoristas = Venta::where('vendedor_id', $juan->id)
            ->whereHas('cliente', fn($q) => $q->where('tipo_cliente', 'MAYORISTA'))
            ->count();
        $this->command->info("Ventas con clientes mayoristas: " . $ventasMayoristas);
    }
}
