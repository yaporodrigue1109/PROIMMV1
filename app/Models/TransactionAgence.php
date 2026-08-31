<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use App\Models\Concerns\VisibleWithActiveProprietaire;
use App\Models\Concerns\VisibleWithActiveLocataire;

class TransactionAgence extends Model
{
    use HasFactory, VisibleWithActiveProprietaire, VisibleWithActiveLocataire;

    protected $table      = 'transaction_agences';
    protected $primaryKey = 'transaction_agence_id';
    public    $incrementing = false;
    protected $keyType    = 'string';
    public    $timestamps = false;

    protected $guarded    = [];

    


    protected $casts = [
        'montant_global_verser' => 'decimal:2',
        'montant_loyer_payer' => 'decimal:2',
        'montant_arriere_payer' => 'decimal:2',
        'montant_arriere_actuel' => 'decimal:2',
        'montant_avance_payer' => 'decimal:2',
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
            $model->created_by = getInfoAgent()->users->id_users ?? null;
            if (empty($model->numero_recu)) {
                $settings = ! empty($model->agence_id)
                    ? ParametrageAgence::where('agence_id', $model->agence_id)->lockForUpdate()->first()
                    : null;
                $model->numero_recu = $settings
                    ? $settings->getNextFactureNumber()
                    : static::genererReferenceUnique();
            }
        });
    }

      const STATUT_LOYER  = 'loyer';
    const STATUT_MAINTENANCE = 'maintenance';
    const STATUT_DEPENSE    = 'depense';
    const STATUT_VENTE    = 'vente';

    // ─── Relations ────────────────────────────────────────────────

    public function locataire() { return $this->belongsTo(Locataire::class,  'locataire_id'); }
    public function loyer()     { return $this->belongsTo(Loyer::class,      'loyer_id'); }
    public function porte()     { return $this->belongsTo(Porte::class,      'porte_id'); }
    public function propriete() { return $this->belongsTo(Propriete::class, 'propriete_id'); }
    public function agence()    { return $this->belongsTo(Agence::class,      'agence_id'); }
    public function modePaiement()  { return $this->belongsTo(ModePaiement::class,  'mode_paiement_id'); }

    public static function genererReferenceUnique(): string
    {
        do {
            $reference = 'REC-' . strtoupper(bin2hex(random_bytes(4)));
        } while (static::withoutGlobalScopes()->where('numero_recu', $reference)->exists());

        return $reference;
    }

    }
