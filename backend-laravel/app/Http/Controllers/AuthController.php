<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $usuario = User::where('username', $credentials['username'])->first();

        if (! $usuario) {
            return response()->json(['error' => 'Usuario no encontrado'], 401);
        }

        if (! Hash::check($credentials['password'], $usuario->password)) {
            return response()->json(['error' => 'Contraseña incorrecta'], 401);
        }

        $datosUsuario = $usuario->makeHidden('password')->toArray();

        return response()->json([
            ...$datosUsuario,
            'usuario' => $datosUsuario,
        ]);
    }
}
