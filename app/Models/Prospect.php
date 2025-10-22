<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Prospect extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'added_by_user_id',
        'name',
        'phone_number',
        'email',
        'status',
        'notes',
    ];

    // --- RELACIONES ---

    /** Un prospecto es agregado por un usuario (agente). */
    public function addedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'added_by_user_id');
    }

    /** Un prospecto puede tener muchas búsquedas asociadas. */
    public function searchHistories(): HasMany
    {
        return $this->hasMany(SearchHistory::class, 'prospect_id');
    }

    /**
     * Define una relación para obtener solo la búsqueda más reciente del prospecto.
     * ESTE ES EL MÉTODO QUE FALTA.
     */
    public function latestSearch(): HasOne
    {
        return $this->hasOne(SearchHistory::class)->latestOfMany();
    }
}
