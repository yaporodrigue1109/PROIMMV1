<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class VenteEcheance extends Model
{
    protected $table = 'vente_echeances';
    protected $primaryKey = 'vente_echeance_id';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $guarded = [];

    protected $casts = [
        'date_echeance' => 'date',
        'montant_prevu' => 'decimal:2',
        'montant_paye' => 'decimal:2',
        'montant_amende' => 'decimal:2',
        'paye_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function ($model) {
            if (! $model->getKey()) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }

    public function vente()
    {
        return $this->belongsTo(VenteBien::class, 'vente_id', 'id_vente');
    }

    public function modePaiement()
    {
        return $this->belongsTo(ModePaiement::class, 'mode_paiement_id');
    }
}
