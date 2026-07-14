<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Loyer extends Model
{
    use HasFactory;

    protected $table      = 'loyer';
    protected $primaryKey = 'loyer_id';
    public    $incrementing = false;
    protected $keyType    = 'string';

    const STATUT_IMPAYE  = 'Paiement en retard';
    const STATUT_PARTIEL = 'Paiement partiel';
    const STATUT_PAYE    = 'Paiement total';
    const STATUT_EN_COURS    = 'Paiement en cours';

protected $guarded    = [];

//    protected $fillable = [
//        'agence_id', 'propriete_id', 'batiment_id', 'lot_id',
//        'porte_id', 'locataire_id', 'proprietaire_id',
//        'mois_paiement', 'annee_paiement',
//        'date_limit_paiement', 'date_paiement',
//        'montant_a_payer', 'montant_payer', 'montant_restant',
//        'arriere_actuel',
//        'montant_agence', 'montant_proprio',
//        'montant_global_agence', 'montant_global_proprio','montant_penalite',
//        'statut', 'is_first', 'commentaire',
//        'created_by', 'updated_by',
//    ];

    protected $casts = [
        'montant_a_payer'       => 'decimal:2',
        'montant_paye'          => 'decimal:2',
        'montant_restant'       => 'decimal:2',
        'arriere_actuel'        => 'decimal:2',
        'montant_agence'        => 'decimal:2',
        'montant_proprio'       => 'decimal:2',
        'montant_global_agence' => 'decimal:2',
        'montant_global_proprio'=> 'decimal:2',
        'is_first'              => 'boolean',
        'date_limit_paiement'   => 'date',
        'date_paiement'         => 'date',
    ];

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function ($model) {

            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }

    // ─── Relations ────────────────────────────────────────────────
    public function scopeImpayesOuPartiels($query)
    {
        return $query->whereIn('statut', ['Paiement en retard', 'Paiement partiel']);
    }
    public function locataire()  { return $this->belongsTo(Locataire::class,  'locataire_id'); }
    public function modePaiement()  { return $this->belongsTo(ModePaiement::class,  'mode_paiement_id'); }
    public function porte()      { return $this->belongsTo(Porte::class,      'porte_id'); }
    public function propriete()  { return $this->belongsTo(Propriete::class,  'propriete_id'); }
    public function transactions(){ return $this->hasMany(Transaction::class, 'loyer_id'); }

    // ─── Accesseurs ───────────────────────────────────────────────

    public function getEstPayeAttribute(): bool    { return $this->statut === self::STATUT_PAYE; }
    public function getEstPartielAttribute(): bool { return $this->statut === self::STATUT_PARTIEL; }
    public function getEstImpayeAttribute(): bool  { return $this->statut === self::STATUT_IMPAYE; }
    public function getEstCoursAttribute(): bool  { return $this->statut === self::STATUT_EN_COURS; }


    public function getPeriodeAttribute(): string
    {
        $mois = ['Jan','Fév','Mar','Avr','Mai','Juin','Juil','Aoû','Sep','Oct','Nov','Déc'];
        return ($mois[$this->mois_paiement - 1] ?? '') . ' ' . $this->annee_paiement;
    }

    // ─── Scopes ───────────────────────────────────────────────────

    public function scopeImpaye($q)  { return $q->where('statut', self::STATUT_IMPAYE); }
    public function scopePartiel($q) { return $q->where('statut', self::STATUT_PARTIEL); }
    public function scopePaye($q)    { return $q->where('statut', self::STATUT_PAYE); }

    public function scopePeriode($q, int $mois, int $annee)
    {
        return $q->where('mois_paiement', $mois)->where('annee_paiement', $annee);
    }
}