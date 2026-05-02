<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClienteController extends Controller
{
    /** GET /clientes - Lista todos los clientes (con filtros opcionales) */
    public function index(Request $request): JsonResponse
    {
        $vendedorId = $request->query('vendedor_id');
        $query = Cliente::orderBy('nombre')
            ->when($vendedorId, fn ($q) => $q->where('clientes.vendedor_id', (int) $vendedorId));

        if ($request->has('tipo') && $request->tipo !== 'TODOS') {
            $query->where('tipo_cliente', $request->tipo);
        }

        if ($request->has('busqueda') && $request->busqueda !== '') {
            $term = $request->busqueda;
            $query->where(function ($q) use ($term) {
                $q->where('nombre', 'like', "%{$term}%")
                    ->orWhere('telefono', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%")
                    ->orWhere('pais', 'like', "%{$term}%")
                    ->orWhere('ciudad', 'like', "%{$term}%")
                    ->orWhere('departamento', 'like', "%{$term}%")
                    ->orWhere('region_estado', 'like', "%{$term}%")
                    ->orWhere('codigo_postal', 'like', "%{$term}%")
                    ->orWhere('moneda_preferida', 'like', "%{$term}%");
            });
        }

        return response()->json($query->get());
    }

    /** POST /clientes - Crea un nuevo cliente */
    public function store(Request $request): JsonResponse
    {
        $datos = $request->validate($this->rules($request, true));

        $cliente = Cliente::create($this->normalizarDatos($datos));

        return response()->json($cliente, 201);
    }

    /** PUT /clientes/{id} - Actualiza un cliente */
    public function update(Request $request, int $id): JsonResponse
    {
        $cliente = Cliente::findOrFail($id);

        $datos = $request->validate($this->rules($request, false));

        $cliente->update($this->normalizarDatos($datos));

        return response()->json($cliente);
    }

    /** GET /clientes/{id} - Detalle de un cliente */
    public function show(int $id): JsonResponse
    {
        return response()->json(Cliente::findOrFail($id));
    }

    private function rules(Request $request, bool $includeSeller): array
    {
        $esColombia = strtolower((string) $request->input('pais', 'Colombia')) === 'colombia';

        $rules = [
            'nombre' => 'required|string|max:120',
            'telefono' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:120',
            'direccion' => 'nullable|string|max:255',
            'pais' => 'required|string|max:100',
            'departamento' => $esColombia ? 'required|string|max:100' : 'nullable|string|max:100',
            'region_estado' => $esColombia ? 'nullable|string|max:120' : 'nullable|string|max:120',
            'ciudad' => 'required|string|max:100',
            'codigo_postal' => 'nullable|string|max:20',
            'moneda_preferida' => 'nullable|string|size:3',
            'tipo_cliente' => 'required|in:MAYORISTA,DETAL',
        ];

        if ($includeSeller) {
            $rules['vendedor_id'] = 'nullable|exists:users,id';
        }

        return $rules;
    }

    private function normalizarDatos(array $datos): array
    {
        $datos['pais'] = $datos['pais'] ?? 'Colombia';
        $datos['moneda_preferida'] = isset($datos['moneda_preferida']) && $datos['moneda_preferida'] !== ''
            ? strtoupper($datos['moneda_preferida'])
            : null;

        if (strtolower((string) $datos['pais']) === 'colombia') {
            $datos['region_estado'] = null;
            $datos['codigo_postal'] = null;
            if (empty($datos['moneda_preferida'])) {
                $datos['moneda_preferida'] = 'COP';
            }
        } else {
            $datos['departamento'] = null;
            if (empty($datos['moneda_preferida'])) {
                $datos['moneda_preferida'] = 'USD';
            }
        }

        return $datos;
    }
}
