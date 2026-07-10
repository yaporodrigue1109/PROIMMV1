<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    use SoftDeletes;

    protected $primaryKey = 'transaction_id';

    protected $fillable = [
        'reference',
        'agence_id',
        'abonnement_id',
        'abonnement_historique_id',
        'montant_base_ht',
        'montant_options_ht',
        'montant_total_ht',
        'taux_tva',
        'montant_tva',
        'montant_ttc',
        'duree_mois',
        'periode_debut',
        'periode_fin',
        'options_souscrites',
        'mode_paiement',
        'statut',
        'reference_paiement',
        'date_paiement',
        'date_validation',
        'type_operation',
        'created_by',
        'updated_by',
        'notes',
    ];

    protected $casts = [
        'montant_base_ht'    => 'decimal:2',
        'montant_options_ht' => 'decimal:2',
        'montant_total_ht'   => 'decimal:2',
        'taux_tva'           => 'decimal:2',
        'montant_tva'        => 'decimal:2',
        'montant_ttc'        => 'decimal:2',
        'options_souscrites' => 'array',
        'periode_debut'      => 'date',
        'periode_fin'        => 'date',
        'date_paiement'      => 'datetime',
        'date_validation'    => 'datetime',
    ];

    // ─── Relations ───────────────────────────────────────────────────────────

    public function agence(): BelongsTo
    {
        return $this->belongsTo(Agence::class, 'agence_id', 'agence_id');
    }

    public function abonnement(): BelongsTo
    {
        return $this->belongsTo(Abonnement::class, 'abonnement_id', 'abonnement_id');
    }

    public function abonnementHistorique(): BelongsTo
    {
        return $this->belongsTo(AbonnementHistorique::class, 'abonnement_historique_id');
    }

    // ─── Scopes ──────────────────────────────────────────────────────────────

    public function scopeValidees($query)
    {
        return $query->where('statut', 'validee');
    }

    public function scopeEnAttente($query)
    {
        return $query->where('statut', 'en_attente');
    }

    public function scopePourAgence($query, string $agenceId)
    {
        return $query->where('agence_id', $agenceId);
    }

    // ─── Accessors ───────────────────────────────────────────────────────────

    public function getMontantTtcFormateAttribute(): string
    {
        return number_format($this->montant_ttc, 0, ',', ' ') . ' FCFA';
    }

    public function getStatutBadgeAttribute(): string
    {
        return match ($this->statut) {
            'validee'    => '<span class="badge badge-success">Validée</span>',
            'en_attente' => '<span class="badge badge-warning">En attente</span>',
            'echouee'    => '<span class="badge badge-danger">Échouée</span>',
            'remboursee' => '<span class="badge badge-info">Remboursée</span>',
            'annulee'    => '<span class="badge badge-secondary">Annulée</span>',
            default      => '<span class="badge">Inconnu</span>',
        };
    }
}