<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Maintenance extends Model
{
    use SoftDeletes;

    protected $table = 'maintenance';
    protected $primaryKey = 'maintenance_id';
   public    $incrementing = false;
    protected $keyType    = 'string';

    protected $fillable = [
        'agence_id',
        'proprietaire_id',
        'lot_id',
        'propriete_id',
        'batiment_id',
        'porte_id',
        'titre',
        'description',
        'statut',
        'montant_global',
        'prise_en_charge_par',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'montant_global' => 'decimal:2',
        'created_at'     => 'datetime',
        'updated_at'     => 'datetime',
        'deleted_at'     => 'datetime',
    ];

    // Valeurs possibles pour prise_en_charge_par
    const PRISE_EN_CHARGE_PROPRIETAIRE = 'proprietaire';
    const PRISE_EN_CHARGE_LOCATAIRE    = 'locataire';
    const PRISE_EN_CHARGE_AGENCE       = 'agence';

    // Statuts possibles
    const STATUT_EN_ATTENTE  = 'en_attente';
    const STATUT_EN_COURS    = 'en_cours';
    const STATUT_TERMINE     = 'termine';
    const STATUT_ANNULE      = 'annule';

    // -------------------------------------------------------------------------
    // Relations
    // -------------------------------------------------------------------------

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function ($model) {

            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
            $model->created_by = getInfoAgent()->users->id_users ;
        });

        static::updating(function ($model) {
            $model->updated_by = getInfoAgent()->users->id_users ;
            $model->updated_at = now();
        });

        static::deleting(function ($model) {
            $model->deleted_by = getInfoAgent()->users->id_users ;
            $model->deleted_at = now();
        });

    }

    public function agence(){
        return $this->belongsTo(Agence::class, 'agence_id', 'agence_id');
    }
    public function details(): HasMany
    {
        return $this->hasMany(MaintenanceDetail::class, 'maintenance_id', 'maintenance_id');
    }

    public function proprietaire(): BelongsTo
    {
        return $this->belongsTo(Proprietaire::class, 'proprietaire_id', 'proprietaire_id');
    }

    public function lot(): BelongsTo
    {
        return $this->belongsTo(ProprietaireLot::class, 'lot_id', 'propreietaire_lot_id');
    }

    public function batiment(): BelongsTo
    {
        return $this->belongsTo(Batiment::class, 'batiment_id', 'batiment_id');
    }
    public function propriete(): BelongsTo
    {
        return $this->belongsTo(Propriete::class, 'propriete_id', 'propriete_id');
    }



    public function porte(): BelongsTo
    {
        return $this->belongsTo(Porte::class, 'porte_id', 'porte_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }


    // -------------------------------------------------------------------------
    // Accesseurs / Helpers
    // -------------------------------------------------------------------------

    /**
     * Recalcule et met à jour le montant_global à partir des détails.
     */
    public function syncMontantGlobal(): void
    {
        $this->montant_global = $this->details()->sum('montant');
        $this->save();
    }

    public static function scopeWithDefaultRelations(): \Illuminate\Database\Eloquent\Builder
    {
        return static::with([

            'updatedBy',
            'createdBy',
            'porte',
            'batiment',
            'proprietaire',
            'lot',
            'agence',
            'propriete',
            'details',
            'details.maintenancier',
            'details.typeIntervention'

        ]);
    }
}