<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Abonnement extends Model
{
    use SoftDeletes;

    protected $primaryKey = 'abonnement_id';
    public $incrementing = false;
    protected $keyType = 'int';

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
        'type',
        'agence_id',
        'ancien_abonnement_id',
        'nouvel_abonnement_id',
        'ancienne_date_debut',
        'ancienne_date_fin',
        'nouvelle_date_debut',
        'nouvelle_date_fin',
        'duree_mois',
        'montant_ht',
        'action',
        'action_par',
        'notes',
    ];

    protected $casts = [
        'prix_mensuel_ht'     => 'decimal:2',
        'prix_annuel_ht'      => 'decimal:2',
        'module_comptabilite' => 'boolean',
        'module_reporting'    => 'boolean',
        'module_api'          => 'boolean',
        'is_default'          => 'boolean',
        'features'            => 'array',
        'ancienne_date_debut' => 'date',
        'ancienne_date_fin'   => 'date',
        'nouvelle_date_debut' => 'date',
        'nouvelle_date_fin'   => 'date',
        'montant_ht'          => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $abonnement) {
            if (empty($abonnement->{$abonnement->getKeyName()})) {
                $nextId = (int) (DB::table($abonnement->getTable())->max($abonnement->getKeyName()) ?? 0) + 1;
                $abonnement->{$abonnement->getKeyName()} = $nextId;
            }
        });
    }

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

    public function agence(): BelongsTo
    {
        return $this->belongsTo(Agence::class, 'agence_id', 'agence_id');
    }

    public function ancienAbonnement(): BelongsTo
    {
        return $this->belongsTo(self::class, 'ancien_abonnement_id', 'abonnement_id');
    }

    public function nouvelAbonnement(): BelongsTo
    {
        return $this->belongsTo(self::class, 'nouvel_abonnement_id', 'abonnement_id');
    }

    public function scopeActifs($query)
    {
        return $query->where('statut', 'actif');
    }

    public function getPrixMensuelFormateAttribute(): string
    {
        return number_format($this->prix_mensuel_ht, 0, ',', ' ') . ' FCFA';
    }

    public function getPrixPourDuree(int $dureeMois): float
    {
        return $this->prix_mensuel_ht * $dureeMois;
    }
}
