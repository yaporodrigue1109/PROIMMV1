<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class TransactionAgence extends Model
{
    use HasFactory;

    protected $table      = 'transaction_agences';
    protected $primaryKey = 'transaction_agence_id';
    public    $incrementing = false;
    protected $keyType    = 'string';
    public    $timestamps = false;

    protected $guarded    = [];

    
//    protected $fillable = [
//        'agence_id', 'propriete_id', 'batiment_id', 'porte_id',
//        'locataire_id', 'loyer_id','proprietaire_id',
//        'montant_total_verse', 'montant_loyer_payer', 'montant_arriere_paye',
//        'arriere_precedent', 'arriere_restant',
//        'is_reversement', 'date_reversement',
//        'mois_payer', 'annee_payer',
//        'date_transaction', 'mode_paiement', 'reference_paiement',
//        'commentaire', 'created_by',
//    ];

    protected $casts = [
        'montant_total_verse'   => 'decimal:2',
        'montant_loyer_payer'   => 'decimal:2',
        'montant_arriere_paye'  => 'decimal:2',
        'arriere_precedent'     => 'decimal:2',
        'arriere_restant'       => 'decimal:2',
        'is_reversement'        => 'boolean',
        'date_reversement'      => 'date',
        'date_transaction'      => 'datetime',
        'created_at'            => 'datetime',
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

    public function locataire() { return $this->belongsTo(Locataire::class,  'locataire_id'); }
    public function loyer()     { return $this->belongsTo(Loyer::class,      'loyer_id'); }
    public function porte()     { return $this->belongsTo(Porte::class,      'porte_id'); }
    public function agence()    { return $this->belongsTo(Agence::class,      'agence_id'); }
    public function modePaiement()  { return $this->belongsTo(ModePaiement::class,  'mode_paiement_id'); }
}