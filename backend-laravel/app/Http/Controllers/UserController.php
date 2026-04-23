<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(): JsonResponse
    {
        $usuarios = User::orderByDesc('id')->get()->makeHidden('password');

        return response()->json($usuarios);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'apellido' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users,username'],
            'password' => ['required', 'string', 'min:4'],
            'rol' => ['nullable', 'string', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:255'],
            'cedula' => ['nullable', 'string', 'max:255'],
            'activo' => ['nullable', 'boolean'],
        ]);

        $usuario = User::create([
            ...$data,
            'password' => Hash::make($data['password']),
            'activo' => $data['activo'] ?? true,
        ]);

        return response()->json($usuario->makeHidden('password'));
    }

    public function show(int $id): JsonResponse
    {
        $usuario = User::find($id);

        if (! $usuario) {
            return response()->json(['error' => 'Usuario no encontrado'], 404);
        }

        return response()->json($usuario->makeHidden('password'));
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $usuario = User::find($id);

        if (! $usuario) {
            return response()->json(['error' => 'Usuario no encontrado'], 404);
        }

        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'apellido' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', Rule::unique('users', 'username')->ignore($usuario->id)],
            'password' => ['nullable', 'string', 'min:4'],
            'rol' => ['nullable', 'string', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:255'],
            'cedula' => ['nullable', 'string', 'max:255'],
            'activo' => ['required', 'boolean'],
        ]);

        if (! empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $usuario->update($data);

        return response()->json($usuario->fresh()->makeHidden('password'));
    }

    public function destroy(int $id): JsonResponse
    {
        $usuario = User::find($id);

        if (! $usuario) {
            return response()->json(['error' => 'Usuario no encontrado'], 404);
        }

        $usuario->delete();

        return response()->json(['message' => 'Usuario eliminado']);
    }
}
