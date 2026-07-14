<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Abonnement extends Model
{
    use SoftDeletes;

    protected $primaryKey = 'abonnement_id';

    protected $fillable = [
        'code_abonnement',
        'name',
        'description',
        'prix_mensuel_ht',
        'prix_annuel_ht',
        'nb_proprietes_max',
        'nb_locataires_max',
        'nb_utilisateurs_max',
        'module_comptabilite',
        'module_reporting',
        'module_api',
        'statut',
        'is_default',
        'ordre',
        'features',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'prix_mensuel_ht'     => 'decimal:2',
        'prix_annuel_ht'      => 'decimal:2',
        'module_comptabilite' => 'boolean',
        'module_reporting'    => 'boolean',
        'module_api'          => 'boolean',
        'is_default'          => 'boolean',
        'features'            => 'array',
    ];

    // ─── Relations ───────────────────────────────────────────────────────────

    public function agences(): HasMany
    {
        return $this->hasMany(Agence::class, 'abonnement_id', 'abonnement_id');
    }

    public function historiques(): HasMany
    {
        return $this->hasMany(AbonnementHistorique::class, 'nouvel_abonnement_id', 'abonnement_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'abonnement_id', 'abonnement_id');
    }

    // ─── Scopes ──────────────────────────────────────────────────────────────

    public function scopeActifs($query)
    {
        return $query->where('statut', 'actif');
    }

    // ─── Accessors ───────────────────────────────────────────────────────────

    public function getPrixMensuelFormateAttribute(): string
    {
        return number_format($this->prix_mensuel_ht, 0, ',', ' ') . ' FCFA';
    }

    public function getPrixPourDuree(int $dureeMois): float
    {
        return $this->prix_mensuel_ht * $dureeMois;
    }
}