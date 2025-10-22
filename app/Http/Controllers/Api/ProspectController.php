<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Prospect;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;

class ProspectController extends Controller
{
    /**
     * Display a listing of the resource.
     * Muestra una lista de todos los prospectos del agente autenticado.
     * Ruta: GET /api/prospects
     */
    public function index(): JsonResponse
    {
        // Obtenemos el usuario autenticado (el agente)
        $user = Auth::user();

        // Obtenemos todos los prospectos que fueron agregados por este agente
        // y los ordenamos por el más reciente.
        $prospects = $user->prospects()->latest()->paginate(15);

        return response()->json($prospects);
    }

    /**
     * Store a newly created resource in storage.
     * Guarda un nuevo prospecto en la base de datos.
     * Ruta: POST /api/prospects
     */
    public function store(Request $request): JsonResponse
    {
        // 1. Validar los datos de entrada
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'phone_number' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'status' => 'required|in:activo,contactado,en_negociacion,cerrado,descartado',
            'notes' => 'nullable|string',
        ]);

        // 2. Añadir el ID del agente que está creando el prospecto
        $validatedData['added_by_user_id'] = Auth::id();

        // 3. Crear el prospecto en la base de datos
        $prospect = Prospect::create($validatedData);

        // 4. Devolver el prospecto recién creado con un código de estado 201 (Created)
        return response()->json($prospect, 201);
    }

    /**
     * Display the specified resource.
     * Muestra los detalles de un prospecto específico.
     * Ruta: GET /api/prospects/{prospect}
     */
    public function show(Prospect $prospect): JsonResponse
    {
        // Laravel, a través de la inyección de modelos, ya encontró el prospecto por nosotros.
        // Ahora, verificamos que el agente autenticado sea el "dueño" de este prospecto.
        if (Auth::id() !== $prospect->added_by_user_id) {
            return response()->json(['message' => 'No autorizado'], 403); // 403 Forbidden
        }

        return response()->json($prospect);
    }

    /**
     * Update the specified resource in storage.
     * Actualiza un prospecto existente.
     * Ruta: PUT /api/prospects/{prospect}
     */
    public function update(Request $request, Prospect $prospect): JsonResponse
    {
        // 1. Verificar que el agente autenticado es el "dueño" del prospecto
        if (Auth::id() !== $prospect->added_by_user_id) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        // 2. Validar los datos de entrada (similar a store)
        $validatedData = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'phone_number' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'status' => 'sometimes|required|in:activo,contactado,en_negociacion,cerrado,descartado',
            'notes' => 'nullable|string',
        ]);

        // 3. Actualizar el prospecto
        $prospect->update($validatedData);

        // 4. Devolver el prospecto actualizado
        return response()->json($prospect);
    }

    /**
     * Remove the specified resource from storage.
     * Elimina un prospecto.
     * Ruta: DELETE /api/prospects/{prospect}
     */
    public function destroy(Prospect $prospect): JsonResponse
    {
        // 1. Verificar que el agente autenticado es el "dueño" del prospecto
        if (Auth::id() !== $prospect->added_by_user_id) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        // 2. Eliminar el prospecto
        $prospect->delete();

        // 3. Devolver una respuesta vacía con código 204 (No Content)
        return response()->json(null, 204);
    }
}
