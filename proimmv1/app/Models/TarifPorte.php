<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class TarifPorte extends Model
{
    use HasFactory;

    protected $table = 'tarif_porte';
    protected $primaryKey = 'tarif_id';
    public $incrementing  = false;
    protected $keyType    = 'string';
    public $timestamps = false;

    protected $fillable = [
        'porte_id',
        'mt_loyer',
        'mt_vente',
        'mt_caution',
        'mt_avance',
        'mt_frais_agence',
        'mt_frais_dossier',
        'mt_caution_cie',
        'mt_caution_sodeci',
        'date_effet',
        'is_actif',
    ];

    protected $casts = [
        'mt_loyer'          => 'decimal:2',
        'mt_vente'          => 'decimal:2',
        'mt_caution'        => 'decimal:2',
        'mt_avance'         => 'decimal:2',
        'mt_frais_agence'   => 'decimal:2',
        'mt_frais_dossier'  => 'decimal:2',
        'mt_caution_cie'    => 'decimal:2',
        'mt_caution_sodeci' => 'decimal:2',
        'date_effet'        => 'date',
        'is_actif'          => 'boolean',
        'created_at'        => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($tarifporte) {
            if (empty($tarifporte->{$tarifporte->getKeyName()})) {
                $tarifporte->{$tarifporte->getKeyName()} = (string) Str::uuid();
            }
        });

    }
    // ─── Relations ────────────────────────────────────────────────

    public function porte()
    {
        return $this->belongsTo(Porte::class, 'porte_id');
    }

    // ─── Accesseurs ───────────────────────────────────────────────

    /** Total à payer à l'entrée (caution + avance + frais + dossier + CIE + SODECI) */
    public function getMontantEntreeAttribute(): float
    {
        return (float) ($this->mt_caution
            + $this->mt_avance
            + $this->mt_frais_agence
            + $this->mt_frais_dossier
            + $this->mt_caution_cie
            + $this->mt_caution_sodeci);
    }

    // ─── Scopes ───────────────────────────────────────────────────

    public function scopeActif($query)
    {
        return $query->where('is_actif', true);
    }
}
