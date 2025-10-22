<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    /**
     * Maneja la solicitud de inicio de sesión del usuario.
     */
    public function login(Request $request): JsonResponse
    {
        // 1. Validar la solicitud
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // 2. Intentar autenticar al usuario
        if (Auth::attempt($credentials)) {
            // 3. Si las credenciales son correctas, obtenemos el usuario
            $user = Auth::user();

            // 4. Creamos un nuevo token de API para este usuario
            $token = $user->createToken('auth_token')->plainTextToken;

            // 5. Devolvemos una respuesta exitosa con el token y los datos del usuario
            return response()->json([
                'user' => $user,
                'token' => $token,
                'token_type' => 'Bearer',
            ]);
        }

        // 6. Si las credenciales son incorrectas, devolvemos un error
        return response()->json([
            'message' => 'Credenciales inválidas.'
        ], 401); // 401 Unauthorized
    }

    /**
     * Maneja la solicitud de cierre de sesión del usuario.
     */
    public function logout(Request $request): JsonResponse
    {
        // Revoca el token de API que se utilizó para autenticar la solicitud actual.
        // Esto invalida el token, forzando al usuario a iniciar sesión de nuevo.
        $request->user()->currentAccessToken()->delete();

        // Devuelve una respuesta de éxito.
        return response()->json([
            'message' => 'Sesión cerrada exitosamente.'
        ]);
    }
}
