<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Propriete extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'propriete';
    protected $primaryKey = 'propriete_id';
    public $incrementing  = false;
    protected $keyType    = 'string';
    protected $fillable = [
        'proprietaire_id',
        'agence_id',
        'lot_id',
        'type_propriete_id',
        'reference',
        'description',
        'adresse_complete',
        'videos_url',
        'is_allocation',
        'is_actif',
        'prossimites'
    ];

    protected $casts = [
        'is_allocation' => 'boolean',
        'is_actif'      => 'boolean',
        'videos_url'    => 'array',
    ];


    protected static function boot()
    {
        parent::boot();

        static::creating(function ($propriete) {
            if (empty($propriete->{$propriete->getKeyName()})) {
                $propriete->{$propriete->getKeyName()} = (string) Str::uuid();
            }
            $propriete->created_by = getInfoAgent()->users->id_users ?? getInfoAdmin()->admin->id_admin ;
        });

        static::updating(function ($propriete) {
            $propriete->updated_by = getInfoAgent()->users->id_users ?? getInfoAdmin()->admin->id_admin ;
        });

    }


    // ─── Relations ────────────────────────────────────────────────

    public function proprietaire()
    {
        return $this->belongsTo(Proprietaire::class, 'proprietaire_id');
    }

    public function agence()
    {
        return $this->belongsTo(Agence::class, 'agence_id');
    }

    public function lot()
    {
        return $this->belongsTo(ProprietaireLot::class, 'lot_id');
    }

    public function typePropriete()
    {
        return $this->belongsTo(TypePropriete::class, 'type_propriete_id');
    }

    public function batiments()
    {
        return $this->hasMany(Batiment::class, 'propriete_id');
    }

    public function proprieteProximites()
    {
        return $this->hasMany(ProprieteProximite::class, 'propriete_id', 'propriete_id');
    }

    // ─── Accesseurs ───────────────────────────────────────────────

    /** Nombre total de portes toutes pièces confondues */
    public function getNbrePorteTotalAttribute(): int
    {
        return $this->batiments->sum(fn($b) => $b->portes->count());
    }

    /** Nombre de portes libres */
    public function getNbrePorteLibreAttribute(): int
    {
        return $this->batiments->sum(fn($b) => $b->portes->where('is_occupe', false)->count());
    }

    /** Nombre de portes occupées */
    public function getNbrePorteOccupeAttribute(): int
    {
        return $this->batiments->sum(fn($b) => $b->portes->where('is_occupe', true)->count());
    }

    public function getNbreBatimentAttribute(): int
    {
        return $this->batiments->count();
    }

    // ─── Scopes ───────────────────────────────────────────────────

    public function scopeActif($query)
    {
        return $query->where('is_actif', true);
    }

    public function scopeAllocation($query)
    {
        return $query->where('is_allocation', true);
    }

    public static function withDefaultRelations(): \Illuminate\Database\Eloquent\Builder
    {
        return static::with([
            'typePropriete',
            'agence',
            'batiments',
            'lot',
            'proprietaire',
            'proprieteProximites.proximite',
            'batiments.portes',
            'batiments.portes.typePorte',
            'batiments.portes.tarifActif',
        ]);
    }
}
