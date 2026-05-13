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

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'precioCorte' => ['nullable', 'numeric', 'min:0'],
            'precioArmado' => ['nullable', 'numeric', 'min:0'],
            'precioCostura' => ['nullable', 'numeric', 'min:0'],
            'precioSoladura' => ['nullable', 'numeric', 'min:0'],
            'precioEmplantillado' => ['nullable', 'numeric', 'min:0'],
        ]);

        $nombre = strtoupper(trim((string) $data['nombre']));

        $tarifa = TarifaCategoria::query()->updateOrCreate(
            ['nombre' => $nombre],
            [
                'precio_corte' => (int) ($data['precioCorte'] ?? 0),
                'precio_armado' => (int) ($data['precioArmado'] ?? 0),
                'precio_costura' => (int) ($data['precioCostura'] ?? 0),
                'precio_soladura' => (int) ($data['precioSoladura'] ?? 0),
                'precio_emplantillado' => (int) ($data['precioEmplantillado'] ?? 0),
            ]
        );

        return response()->json($this->formatTarifa($tarifa), $tarifa->wasRecentlyCreated ? 201 : 200);
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
