<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use App\Models\Concerns\VisibleWithActiveProprietaire;

class Reversement extends Model
{
    use SoftDeletes, VisibleWithActiveProprietaire;

    protected $primaryKey = 'id_reversement';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $table = 'reversements';

    protected $fillable = [
        'id_reversement',
        'lot_id',
        'vente_id',
        'type_reversement',
        'proprietaire_id',
        'agence_id',
        'periode_debut',
        'periode_fin',
        'total_attendu',
        'total_encaisse',
        'total_restant',
        'total_loyer_paye',
        'total_arriere_paye',
        'taux_commission',
        'montant_commission',
        'montant_apres_commission',
        'nouvelle_caution',
        'depenses_effectuees',
        'frais_dossier',
        'montant_maintenances',
        'cautionSodeci',
        'net_a_reverser',
        'statut',
        'date_reversement',
        'mode_paiement',
        'reference_paiement',
        'numero_cheque',
        'observation',
        'signe_par',
        'date_signature',
        'created_by',
        'updated_by',
        'deleted_by'
    ];

    protected $casts = [
        'periode_debut' => 'date',
        'periode_fin' => 'date',
        'date_reversement' => 'date',
        'date_signature' => 'datetime',
        'total_attendu' => 'integer',
        'total_encaisse' => 'integer',
        'total_restant' => 'integer',
        'total_loyer_paye' => 'integer',
        'total_arriere_paye' => 'integer',
        'taux_commission' => 'decimal:2',
        'montant_commission' => 'integer',
        'montant_apres_commission' => 'integer',
        'nouvelle_caution' => 'integer',
        'cautionSodeci' => 'integer',   
        'depenses_effectuees' => 'integer',
        'frais_dossier' => 'integer',
        'montant_maintenances' => 'integer',
        'net_a_reverser' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    protected $attributes = [
        'statut' => 'en_attente',
        'taux_commission' => 10.00
    ];



        protected static function boot(): void
    {
        parent::boot();
        static::creating(function ($model) {

            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
            $model->created_by = getInfoAgent()?->users?->id_users
                ?? getInfoAdmin()?->admin?->id_admin
                ?? 'system';
        });

        static::updating(function ($model) {
            $model->updated_by = getInfoAgent()?->users?->id_users
                ?? getInfoAdmin()?->admin?->id_admin
                ?? 'system';
            $model->updated_at = now();
        });

        static::deleting(function ($model) {
            $model->deleted_by = getInfoAgent()?->users?->id_users
                ?? getInfoAdmin()?->admin?->id_admin
                ?? 'system';
            $model->deleted_at = now();
        });

    }

    // Relations
    public function details(): HasMany
    {
        return $this->hasMany(ReversementDetail::class, 'reversement_id', 'id_reversement');
    }

    public function lot(): BelongsTo
    {
        return $this->belongsTo(ProprietaireLot::class, 'lot_id', 'propreietaire_lot_id');
    }

    public function vente(): BelongsTo
    {
        return $this->belongsTo(VenteBien::class, 'vente_id', 'id_vente');
    }

    public function proprietaire(): BelongsTo
    {
        return $this->belongsTo(Proprietaire::class, 'proprietaire_id', 'proprietaire_id');
    }

    public function agence(): BelongsTo
    {
        return $this->belongsTo(Agence::class, 'agence_id', 'agence_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'id_user');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by', 'id_user');
    }


        public static function withDefaultRelations(): \Illuminate\Database\Eloquent\Builder
    {
        return static::with([
            'proprietaire',
            'agence',
            'createdBy',
            'updatedBy',
            'lot',
            'details'

        ]);
    }

    // Scopes
    public function scopeEnAttente($query)
    {
        return $query->where('statut', 'en_attente');
    }

    public function scopeReverse($query)
    {
        return $query->where('statut', 'reverse');
    }

    public function scopeAnnule($query)
    {
        return $query->where('statut', 'annule');
    }

    public function scopeByAgence($query, $agenceId)
    {
        return $query->where('agence_id', $agenceId);
    }

    public function scopeByProprietaire($query, $proprietaireId)
    {
        return $query->where('proprietaire_id', $proprietaireId);
    }

    public function scopeByLot($query, $lotId)
    {
        return $query->where('lot_id', $lotId);
    }

    public function scopeInPeriode($query, $dateDebut, $dateFin)
    {
        return $query->where('periode_debut', '>=', $dateDebut)
                     ->where('periode_fin', '<=', $dateFin);
    }

    // Accessors
    public function getPourcentageEncaisseAttribute(): float
    {
        if ($this->total_attendu == 0) {
            return 0;
        }
        return round(($this->total_encaisse / $this->total_attendu) * 100, 2);
    }

    public function getEstCompletAttribute(): bool
    {
        return $this->total_restant <= 0 && $this->statut === 'en_attente';
    }

    // Mutators
    public function setTauxCommissionAttribute($value)
    {
        $this->attributes['taux_commission'] = round($value, 2);
    }

    // Méthodes métier
    public function calculerTotaux(): self
    {
        $this->total_attendu = $this->total_loyer_paye + $this->total_arriere_paye;
        $this->total_restant = $this->total_attendu - $this->total_encaisse;
        return $this;
    }

    public function calculerCommission(): self
    {
        $this->montant_commission = (int) round($this->total_encaisse * ($this->taux_commission / 100));
        $this->montant_apres_commission = $this->total_encaisse - $this->montant_commission;
        $this->net_a_reverser = $this->montant_apres_commission + $this->nouvelle_caution - $this->depenses_effectuees;
        return $this;
    }

    public function updateTotalsFromDetails(): self
    {
        $this->total_loyer_paye = $this->details->sum('loyer_paye');
        $this->total_arriere_paye = $this->details->sum('arriere_paye');
        $this->total_encaisse = $this->details->sum('total_paye');
        
        $this->calculerTotaux();
        $this->calculerCommission();
        
        return $this;
    }

    public function valider(string $modePaiement, ?string $referencePaiement = null, ?string $signePar = null): self
    {
        if ($this->statut === 'reverse') {
            throw new \Exception('Ce reversement a déjà été effectué');
        }

        if ($this->total_restant > 0) {
            throw new \Exception('Impossible de valider un reversement avec des impayés');
        }

        $this->statut = 'reverse';
        $this->date_reversement = now();
        $this->mode_paiement = $modePaiement;
        $this->reference_paiement = $referencePaiement;
        $this->signe_par = $signePar;
        $this->date_signature = now();

        return $this;
    }

    public function annuler(string $motif): self
    {
        if ($this->statut === 'reverse') {
            throw new \Exception('Impossible d\'annuler un reversement déjà effectué');
        }

        $this->statut = 'annule';
        $this->observation = 'Annulé: ' . $motif;

        return $this;
    }

    public function estReversible(): bool
    {
        return $this->statut === 'en_attente' && $this->total_restant <= 0;
    }

    // Génération d'ID
    public static function generateId(): string
    {
        return 'REV-' . time() . '-' . substr(uniqid(), 0, 8);
    }
}
