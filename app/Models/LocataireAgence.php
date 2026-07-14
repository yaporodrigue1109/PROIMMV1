<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class LocataireAgence extends Model
{
    use HasFactory;

    protected $table      = 'locataire_agence';
    protected $primaryKey = 'locataire_agence_id';
    public    $incrementing = false;
    protected $keyType    = 'string';

    protected $fillable = [
        'agence_id', 'locataire_id', 'proprietaire_id',
        'propriete_id', 'batiment_id', 'lot_id', 'porte_id',
        'nbre_personne',
        'nbre_caution', 'nbre_avance', 'nbre_agence',
        'loyer_net', 'caution', 'avance', 'agence',
        'caution_cie', 'caution_sodeci', 'frais_de_dossier',
        'pas_de_porte', 'montant_global_garantie',
        'versements_depot_garantie', 'mode_paiement_id', 'periodicite_paiement_id',
        'is_active', 'is_new',
        'civilite_representant_id', 'name_representant',
        'adresse_representant', 'contant_representant', 'nbre_enfant',
        'date_debut_bail', 'date_fin_bail', 'date_entree', 'date_signature_bail',
        'created_by', 'updated_by',
    ];

    protected $casts = [
        'is_active'      => 'boolean',
        'is_new'         => 'boolean',
        'loyer_net'      => 'decimal:2',
        'caution'        => 'decimal:2',
        'avance'         => 'decimal:2',
        'agence'         => 'decimal:2',
        'caution_cie'    => 'decimal:2',
        'caution_sodeci' => 'decimal:2',
        'frais_de_dossier' => 'decimal:2',
        'pas_de_porte'   => 'decimal:2',
        'montant_global_garantie' => 'decimal:2',
        'versements_depot_garantie' => 'array',
        'mode_paiement_id' => 'integer',
        'periodicite_paiement_id' => 'integer',
        'date_debut_bail'=> 'date',
        'date_fin_bail'  => 'date',
        'date_entree'    => 'date',
        'date_signature_bail' => 'date',
    ];

    protected $appends = [
        'frais_de_dossier',
    ];

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
            $model->created_by = getInfoAgent()->users->id_users ?? null;
        });
        static::updating(function ($model) {
            $model->updated_by = getInfoAgent()->users->id_users ?? null;
        });
    }

    // ─── Relations ────────────────────────────────────────────────

    // ─── Relations ────────────────────────────────────────────────────────────

    public function locataire()
    {
        return $this->belongsTo(Locataire::class, 'locataire_id', 'locataire_id');
    }
    public function loyers()
    {
        return $this->hasMany(Loyer::class, 'locataire_id', 'locataire_id')
            ->where('agence_id', $this->agence_id);
    }
    public function propriete()
    {
        return $this->belongsTo(Propriete::class, 'propriete_id', 'propriete_id');
    }

    public function batiment()
    {
        return $this->belongsTo(Batiment::class, 'batiment_id', 'batiment_id');
    }

    public function lot()
    {
        return $this->belongsTo(ProprietaireLot::class,  'lot_id','propreietaire_lot_id');
    }

    public function porte()
    {
        return $this->belongsTo(Porte::class, 'porte_id', 'porte_id');
    }

    public function proprietaire()
    {
        return $this->belongsTo(Proprietaire::class, 'proprietaire_id', 'proprietaire_id');
    }

    public function periodicitePaiement()
    {
        return $this->belongsTo(PeriodicitePaiement::class, 'periodicite_paiement_id');
    }

    public function modePaiement()
    {
        return $this->belongsTo(ModePaiement::class, 'mode_paiement_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
    public function agence(){
        return $this->belongsTo(Agence::class, 'agence_id', 'agence_id');
    }
    public static function scopeWithDefaultRelations(): \Illuminate\Database\Eloquent\Builder
    {
        return static::with([
            'createdBy',
            'updatedBy',
            'porte',
            'proprietaire',
            'locataire',
            'lot',
            'batiment',
            'agence',
            'porte.loyers'

        ]);
    }



    // ─── Accesseurs ───────────────────────────────────────────────

    /** Montant total à payer à l'entrée */
    public function getMontantEntreeAttribute(): float
    {
      //  $tarif = $this->porte?->tarifActif;
     //   if (!$tarif) return 0;
        return (float)(
           ( ($this->porte->caution  ?? 0)
            + ($this->porte->avance    ?? 0)
            + ($this->porte->agence  ?? 0)
           * $this->porte->mt_loyer
           )
            + $this->porte->mt_caution_cie
            + $this->porte->mt_caution_sodeci
            + ($this->porte->frais_annexe ?? 0)
        );
    }

    public function getFraisDeDossierAttribute()
    {
        return $this->attributes['frais_de_dossier'] ?? $this->attributes['frais_annexe'] ?? null;
    }

    public function setFraisDeDossierAttribute($value): void
    {
        $this->attributes['frais_annexe'] = $value;
    }
}
