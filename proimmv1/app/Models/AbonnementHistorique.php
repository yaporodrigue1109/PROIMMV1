<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class AbonnementHistorique extends Model
{
    protected $fillable = [
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
        'ancienne_date_debut' => 'date',
        'ancienne_date_fin'   => 'date',
        'nouvelle_date_debut' => 'date',
        'nouvelle_date_fin'   => 'date',
        'montant_ht'          => 'decimal:2',
    ];

    // ─── Relations ───────────────────────────────────────────────────────────

    public function agence(): BelongsTo
    {
        return $this->belongsTo(Agence::class, 'agence_id', 'agence_id');
    }

    public function ancienAbonnement(): BelongsTo
    {
        return $this->belongsTo(Abonnement::class, 'ancien_abonnement_id', 'abonnement_id');
    }

    public function nouvelAbonnement(): BelongsTo
    {
        return $this->belongsTo(Abonnement::class, 'nouvel_abonnement_id', 'abonnement_id');
    }

    public function transaction(): HasOne
    {
        return $this->hasOne(Transaction::class, 'abonnement_historique_id');
    }
}