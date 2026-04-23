<?php

namespace App\Http\Controllers;

use App\Models\TarifaCategoria;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TarifaController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(
            TarifaCategoria::query()
                ->orderBy('nombre')
                ->get()
                ->map(fn (TarifaCategoria $tarifa) => $this->formatTarifa($tarifa))
        );
    }

    public function actualizar(Request $request): JsonResponse
    {
        $tarifas = $request->validate([
            '*.nombre' => ['required', 'string'],
            '*.precioCorte' => ['required', 'numeric'],
            '*.precioArmado' => ['required', 'numeric'],
            '*.precioCostura' => ['required', 'numeric'],
            '*.precioSoladura' => ['required', 'numeric'],
            '*.precioEmplantillado' => ['required', 'numeric'],
        ]);

        foreach ($tarifas as $tarifa) {
            TarifaCategoria::where('nombre', $tarifa['nombre'])->update([
                'precio_corte' => (int) $tarifa['precioCorte'],
                'precio_armado' => (int) $tarifa['precioArmado'],
                'precio_costura' => (int) $tarifa['precioCostura'],
                'precio_soladura' => (int) $tarifa['precioSoladura'],
                'precio_emplantillado' => (int) $tarifa['precioEmplantillado'],
            ]);
        }

        return response()->json(['msg' => 'Precios actualizados correctamente']);
    }

    private function formatTarifa(TarifaCategoria $tarifa): array
    {
        return [
            'id' => $tarifa->id,
            'nombre' => $tarifa->nombre,
            'precioCorte' => $tarifa->precio_corte,
            'precioArmado' => $tarifa->precio_armado,
            'precioCostura' => $tarifa->precio_costura,
            'precioSoladura' => $tarifa->precio_soladura,
            'precioEmplantillado' => $tarifa->precio_emplantillado,
        ];
    }
}
