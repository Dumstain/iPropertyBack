<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProspectController;
use App\Http\Controllers\Api\PropertyController;
use App\Http\Controllers\Api\ChatController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// --- Rutas de Autenticación ---

// Ruta pública para iniciar sesión
Route::post('/auth/login', [AuthController::class, 'login']);

// Grupo de rutas protegidas que requieren autenticación con Sanctum
Route::middleware('auth:sanctum')->group(function () {
    // Ruta para cerrar sesión
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    // Ruta de ejemplo para obtener el usuario autenticado (útil para pruebas)
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // --- Rutas para Prospectos ---
    // Esta única línea crea 5 rutas para nosotros:
    // GET /api/prospects          (Listar todos)
    // POST /api/prospects         (Crear nuevo)
    // GET /api/prospects/{id}     (Mostrar uno)
    // PUT/PATCH /api/prospects/{id} (Actualizar uno)
    // DELETE /api/prospects/{id}  (Eliminar uno)
    Route::apiResource('prospects', ProspectController::class); // <--- AÑADE ESTA LÍNEA

    // --- Rutas para Propiedades ---
    // Esta única línea crea 5 rutas para nosotros:
    // GET /api/properties          (Listar todos)
    // POST /api/properties         (Crear nuevo)
    // GET /api/properties/{id}     (Mostrar uno)
    // PUT/PATCH /api/properties/{id} (Actualizar uno)
    // DELETE /api/properties/{id}  (Eliminar uno)
    Route::apiResource('properties', PropertyController::class); // <--- AÑADE ESTA LÍNEA

    // --- Ruta para el Chat de Búsqueda Inteligente ---
    Route::post('/chat/query', [ChatController::class, 'query']); // <--- AÑADE ESTA LÍNEA

    Route::get('/properties/{property}/suggested-prospects', [PropertyController::class, 'suggestedProspects']); // <--- AÑADE ESTA LÍNEA

});
