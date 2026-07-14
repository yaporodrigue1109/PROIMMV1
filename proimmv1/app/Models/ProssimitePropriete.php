<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProssimitePropriete extends Model
{
    use HasFactory;

    protected $table = 'prossimite_proprietes';
    protected $primaryKey = 'id';

    protected $fillable = [
        'agence_id',
        'name',
        'description',
    ];

    // ─── Relations ────────────────────────────────────────────────

    public function agence()
    {
        return $this->belongsTo(Agence::class, 'agence_id');
    }

    public function proprietes()
    {
        return $this->hasMany(Propriete::class, 'type_propriete_id');
    }

    // ─── Scopes ───────────────────────────────────────────────────

    /** Filtrer par agence */
    public function scopeForAgence($query, string $agenceId)
    {
        return $query->where('agence_id', $agenceId);
    }

    // ─── Accesseurs ───────────────────────────────────────────────

    /** Alias "libelle" pour compatibilité avec les vues existantes */
    public function getLibelleAttribute(): string
    {
        return $this->name;
    }
}