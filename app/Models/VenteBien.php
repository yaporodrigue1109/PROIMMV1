<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class VenteBien extends Model
{
    use HasFactory;

    protected $table = 'ventes_biens';
    protected $primaryKey = 'id_vente';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = true;

    protected $guarded = [];

    protected $casts = [
        'prix_vente' => 'decimal:2',
        'commission' => 'decimal:2',
        'montant_proprietaire' => 'decimal:2',
        'acompte_mensuel' => 'decimal:2',
        'date_accord' => 'date',
        'date_premiere_mensualite' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Constantes pour les statuts
    const STATUT_EN_COURS = 'en_cours';
    const STATUT_PARTIEL = 'partiel';
    const STATUT_TERMINE = 'termine';
    const STATUT_ANNULE = 'annule';

    const STATUTS = [
        self::STATUT_EN_COURS,
        self::STATUT_PARTIEL,
        self::STATUT_TERMINE,
        self::STATUT_ANNULE,
    ];

    // Constantes pour les types de paiement
    const PAIEMENT_COMPLET = 'complet';
    const PAIEMENT_TRANCHES = 'tranches';
    const PAIEMENT_MENSUEL = 'mensuel';
    const PAIEMENT_PERSONNALISE = 'personnalise';

    const TYPES_PAIEMENT = [
        self::PAIEMENT_COMPLET,
        self::PAIEMENT_TRANCHES,
        self::PAIEMENT_MENSUEL,
        self::PAIEMENT_PERSONNALISE,
    ];

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
            $model->created_at = now();
            $model->updated_at = now();
        });
    }

    // ─── Relations ────────────────────────────────────────────────

    public function propriete()
    {
        return $this->belongsTo(Propriete::class, 'propriete_id');
    }

    public function batiment()
    {
        return $this->belongsTo(Batiment::class, 'batiment_id');
    }

    public function lot()
    {
        return $this->belongsTo(ProprietaireLot::class, 'lot_id');
    }

    public function porte()
    {
        return $this->belongsTo(Porte::class, 'porte_id');
    }

    public function proprietaire()
    {
        return $this->belongsTo(Proprietaire::class, 'proprietaire_id');
    }

    public function agence()
    {
        return $this->belongsTo(Agence::class, 'agence_id');
    }

    public function acheteur()
    {
        return $this->belongsTo(Acheteur::class, 'acheteur_vente_id', 'id_acheteur');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // ─── Scopes ──────────────────────────────────────────────────

    public function scopeByAgence($query, string $agenceId)
    {
        return $query->where('agence_id', $agenceId);
    }

    public function scopeByProprietaire($query, string $proprietaireId)
    {
        return $query->where('proprietaire_id', $proprietaireId);
    }

    public function scopeByStatut($query, string $statut)
    {
        return $query->where('statut', $statut);
    }

    public function scopeByTypePaiement($query, string $typePaiement)
    {
        return $query->where('type_paiement', $typePaiement);
    }

    public function scopeEnCours($query)
    {
        return $query->where('statut', self::STATUT_EN_COURS);
    }

    public function scopeTermine($query)
    {
        return $query->where('statut', self::STATUT_TERMINE);
    }

    public function scopeAnnule($query)
    {
        return $query->where('statut', self::STATUT_ANNULE);
    }

    // ─── Accesseurs ──────────────────────────────────────────────

    public function getMontantCommissionAttribute()
    {
        if ($this->commission) {
            return $this->commission;
        }
        
        // Calcul automatique si non défini (10% par défaut)
        return $this->prix_vente * 0.10;
    }

    public function getMontantProprietaireAttribute()
    {
        if ($this->montant_proprietaire) {
            return $this->montant_proprietaire;
        }
        
        return $this->prix_vente - $this->montant_commission;
    }

    public function getStatutLabelAttribute()
    {
        $labels = [
            self::STATUT_EN_COURS => 'En cours',
            self::STATUT_PARTIEL => 'Partiel',
            self::STATUT_TERMINE => 'Terminé',
            self::STATUT_ANNULE => 'Annulé',
        ];
        
        return $labels[$this->statut] ?? $this->statut;
    }

    public function getTypePaiementLabelAttribute()
    {
        $labels = [
            self::PAIEMENT_COMPLET => 'Complet',
            self::PAIEMENT_TRANCHES => 'Tranches',
            self::PAIEMENT_MENSUEL => 'Mensuel',
            self::PAIEMENT_PERSONNALISE => 'Personnalisé',
        ];
        
        return $labels[$this->type_paiement] ?? $this->type_paiement;
    }

    // ─── Méthodes ─────────────────────────────────────────────────

    public function isTermine(): bool
    {
        return $this->statut === self::STATUT_TERMINE;
    }

    public function isEnCours(): bool
    {
        return $this->statut === self::STATUT_EN_COURS;
    }

    public function isAnnule(): bool
    {
        return $this->statut === self::STATUT_ANNULE;
    }

    public function isPaiementComplet(): bool
    {
        return $this->type_paiement === self::PAIEMENT_COMPLET;
    }

    public function isPaiementMensuel(): bool
    {
        return $this->type_paiement === self::PAIEMENT_MENSUEL;
    }
}