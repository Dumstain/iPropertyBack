<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Property;
use App\Models\SearchHistory;
use OpenAI\Client;
use Illuminate\Http\JsonResponse;
use Exception;

class ChatController extends Controller
{
    /**
     * Procesa una consulta de búsqueda en lenguaje natural.
     * Ruta: POST /api/chat/query
     */
    public function query(Request $request): JsonResponse
    {
        // 1. Validar la solicitud de entrada
        $validated = $request->validate([
            'message' => 'required|string|min:10|max:500',
            'prospect_id' => 'nullable|integer|exists:prospects,id',
        ]);

        $userQuery = $validated['message'];
        $prospectId = $validated['prospect_id'] ?? null;

        try {
            // --- PARTE A: EL TRADUCTOR (LLAMADA A OPENAI) ---
            $criteria = $this->extractCriteriaFromQuery($userQuery);

            // --- PARTE B: EL ANALISTA (ALGORITMO DE MATCHING) ---
            $allProperties = Property::where('estado', 'disponible')->get();
            $matchedProperties = [];

            foreach ($allProperties as $property) {
                $score = $property->calculateMatchScore($criteria);
                if ($score >= 60) {
                    $property->match_score = $score; // Añadimos el score a la propiedad
                    $matchedProperties[] = $property;
                }
            }

            // --- PARTE C: EL CURADOR (FILTRADO Y ORDENAMIENTO) ---
            // Ordenar por 'match_score' de mayor a menor
            usort($matchedProperties, function ($a, $b) {
                return $b->match_score <=> $a->match_score;
            });

            // Limitar a los 5 mejores resultados
            $topProperties = array_slice($matchedProperties, 0, 5);

            // Guardar en el historial de búsqueda
            SearchHistory::create([
                'user_id' => Auth::id(),
                'prospect_id' => $prospectId,
                'query_text' => $userQuery,
                'extracted_criteria' => $criteria,
                'results_count' => count($topProperties),
                'top_match_score' => $topProperties[0]->match_score ?? 0,
            ]);

            return response()->json($topProperties);

        } catch (Exception $e) {
            // Manejar cualquier error de la API de OpenAI o del proceso
            return response()->json(['message' => 'Error al procesar la búsqueda: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Llama a la API de OpenAI para extraer criterios estructurados.
     */
    private function extractCriteriaFromQuery(string $userQuery): array
    {
        // Inicializamos el cliente de OpenAI con nuestra clave de API
        $client = \OpenAI::client(config('services.openai.secret'));

        $systemPrompt = <<<PROMPT
        Eres un asistente experto inmobiliario. Tu única tarea es extraer criterios de la consulta de un usuario y devolverlos en un formato JSON estricto. No respondas con texto, solo con el JSON.
        Los campos posibles son:
        - "precio_min": integer
        - "precio_max": integer
        - "ubicaciones": array de strings (colonias o fraccionamientos)
        - "habitaciones_total": integer
        - "habitaciones_pb": integer (solo si se menciona explícitamente "planta baja" o similar)
        - "banos_total": integer
        - "jardin_tamano": string ("chico", "mediano", "grande", "cualquiera")
        - "amenidades": array de strings (ej: "alberca", "vigilancia", "terraza")
        Si un criterio no es mencionado, omite el campo del JSON.
        PROMPT;

        $response = $client->chat()->create([
            'model' => 'gpt-4o-mini',
            'response_format' => ['type' => 'json_object'], // Forza la respuesta a ser un JSON válido
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userQuery],
            ],
        ]);

        // Decodificamos la respuesta JSON de OpenAI a un array de PHP
        return json_decode($response->choices[0]->message->content, true);
    }


}
