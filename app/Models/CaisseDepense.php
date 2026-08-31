<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CaisseDepense extends Model
{
    protected $table = 'caisse_depenses';
    protected $primaryKey = 'caisse_depense_id';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $guarded = [];

    protected $casts = [
        'montant' => 'decimal:2',
        'date_depense' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $depense) {
            $depense->caisse_depense_id ??= (string) Str::uuid();
        });
    }

    public function modePaiement()
    {
        return $this->belongsTo(ModePaiement::class, 'mode_paiement_id');
    }
}
