<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class ReversementDetail extends Model
{
   // use SoftDeletes;

    protected $primaryKey = 'id_reversement_detail';
    public $incrementing = false;
    protected $keyType = 'string';
      protected $table = 'reversement_details';

    protected $fillable = [
        'id_reversement_detail',
        'reversement_id',
        'locataire_id',
        'porte_id',
        'agence_id',
        'proprietaire_id',
        'lot_id',
        'propriete_id',
        'batiment_id',
        'montant_loyer',
        'arrieres_init',
        'montant_attendu',
        'loyer_paye',
        'arriere_paye',
        'total_paye',
        'impayes',
        'created_by',
        'updated_by',
         'date_paiement'   ,
        'caution_payee'     ,
        'mois_payer'    ,
        
        'caution_sodeci'   ,
        'date_entree'    ,
       
      
        'nouvelle_caution' ,
        'montant_paye'     ,
       
    ];

    protected $casts = [
        'montant_loyer' => 'integer',
        'arrieres_init' => 'integer',
        'montant_attendu' => 'integer',
        'montant_paye' => 'integer',
        'nouvelle_caution' => 'integer',
        'caution_payee' => 'integer',
        'loyer_paye' => 'integer',
        'arriere_paye' => 'integer',
        'total_paye' => 'integer',
        
        'caution_sodeci' => 'integer',
        'loyer_paye' => 'integer',
        'arriere_paye' => 'integer',
        'total_paye' => 'integer',
        'impayes' => 'integer',
        'created_at' => 'datetime',
        'date_entree' => 'date',
        'date_paiement' => 'date',
        'updated_at' => 'datetime',
       // 'deleted_at' => 'datetime'
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

        // static::deleting(function ($model) {
        //     $model->deleted_by = getInfoAgent()?->users?->id_users
        //         ?? getInfoAdmin()?->admin?->id_admin
        //         ?? 'system';
        //     $model->deleted_at = now();
        // });

    }

    // Relations
    public function reversement(): BelongsTo
    {
        return $this->belongsTo(Reversement::class, 'reversement_id', 'id_reversement');
    }

    public function locataire(): BelongsTo
    {
        return $this->belongsTo(Locataire::class, 'locataire_id', 'locataire_id');
    }

    public function porte(): BelongsTo
    {
        return $this->belongsTo(Porte::class, 'porte_id', 'porte_id');
    }

    public function agence(): BelongsTo
    {
        return $this->belongsTo(Agence::class, 'agence_id', 'agence_id');
    }

    public function proprietaire(): BelongsTo
    {
        return $this->belongsTo(Proprietaire::class, 'proprietaire_id', 'proprietaire_id');
    }

    public function lot(): BelongsTo
    {
        return $this->belongsTo(ProprietaireLot::class, 'lot_id', 'propreietaire_lot_id');
    }

    public function propriete(): BelongsTo
    {
        return $this->belongsTo(Propriete::class, 'propriete_id', 'propriete_id');
    }

    public function batiment(): BelongsTo
    {
        return $this->belongsTo(Batiment::class, 'batiment_id', 'batiment_id');
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
            'porte',
            'agence',
            'proprietaire',
            'lot',
            'propriete',
            'batiment',
            'locataire',
            'reversement',
            'createdBy',
            'updatedBy',

        ]);
    }
    // Scopes
    public function scopeFullyPaid($query)
    {
        return $query->where('impayes', '<=', 0);
    }

    public function scopeWithImpayes($query)
    {
        return $query->where('impayes', '>', 0);
    }

    // Accessors
    public function getEstPayeAttribute(): bool
    {
        return $this->impayes <= 0;
    }

    // Mutators
    public function setLoyerPayeAttribute($value)
    {
        $this->attributes['loyer_paye'] = (int) $value;
        $this->recalculerTotaux();
    }

    public function setArrierePayeAttribute($value)
    {
        $this->attributes['arriere_paye'] = (int) $value;
        $this->recalculerTotaux();
    }

    protected function recalculerTotaux(): void
    {
        if (isset($this->attributes['loyer_paye']) && isset($this->attributes['arriere_paye'])) {
            $this->attributes['total_paye'] = $this->attributes['loyer_paye'] + $this->attributes['arriere_paye'];
            $this->attributes['impayes'] = $this->attributes['montant_attendu'] - $this->attributes['total_paye'];
        }
    }

    // Méthodes métier
    public function calculerTotaux(): self
    {
        $this->total_paye = $this->loyer_paye + $this->arriere_paye;
        $this->impayes = $this->montant_attendu - $this->total_paye;
        return $this;
    }

    public static function generateId(): string
    {
        return 'RD-' . time() . '-' . substr(uniqid(), 0, 8);
    }
}