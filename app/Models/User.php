<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // --- RELACIONES ---

    /** Un usuario (agente) puede listar muchas propiedades. */
    public function properties(): HasMany
    {
        return $this->hasMany(Property::class, 'listed_by_user_id');
    }

    /** Un usuario (agente) puede agregar muchos prospectos. */
    public function prospects(): HasMany
    {
        return $this->hasMany(Prospect::class, 'added_by_user_id');
    }

    /** Un usuario (agente) puede realizar muchas búsquedas. */
    public function searchHistories(): HasMany
    {
        return $this->hasMany(SearchHistory::class, 'user_id');
    }
}
