<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SearchHistory extends Model
{
    use HasFactory;

    protected $table = 'search_history'; // <--- AÑADE ESTA LÍNEA


    /** Esta tabla no usa la columna 'updated_at', así que se lo indicamos a Laravel. */
    public const UPDATED_AT = null;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'prospect_id',
        'query_text',
        'extracted_criteria',
        'results_count',
        'top_match_score',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'extracted_criteria' => 'array',
    ];

    // --- RELACIONES ---

    /** Una búsqueda es realizada por un usuario (agente). */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /** Una búsqueda puede estar asociada a un prospecto. */
    public function prospect(): BelongsTo
    {
        return $this->belongsTo(Prospect::class, 'prospect_id');
    }
}
