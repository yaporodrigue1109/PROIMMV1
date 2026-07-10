<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ParametrageAgence extends Model
{
    use SoftDeletes;

    protected $table = 'parametrages_agence';
    protected $primaryKey = 'parametrages_agence_id';
    public $incrementing  = false;
    protected $keyType    = 'string';


protected $guarded = [];


//    protected $fillable = [
//        'agence_id',
//        // Général
//        'devise', 'langue', 'format_date', 'timezone', 'sauvegarde_auto',
//        'double_validation', 'journal_activites', 'multi_session',
//        // Facturation
//        'periode_facturation', 'jour_emission', 'delai_paiement',
//        'penalite_retard', 'prefixe_facture', 'sequence_facture',
//        'commission', 'base_commission', 'tva', 'aib', 'ras',
//        'acompte_min', 'mode_reglement',
//        // Logos
//        'logo', 'logo_largeur', 'logo_position', 'logo_tutelle',
//        'logo_partenaire', 'cachet',
//        // Signatures
//        'signature_dg', 'dg_nom', 'dg_titre', 'signature_sg', 'sg_nom',
//        'sg_titre', 'signature_cpt', 'cpt_nom', 'cpt_titre',
//        'sig_dg_facture', 'sig_double', 'cachet_auto',
//        // Notifications
//        'notif_rappel', 'notif_retard', 'notif_recu', 'email_compta',
//        'email_dg', 'delai_rappel', 'seuil_dg',
//        // Audit
//        'created_by', 'updated_by'
//    ];

    protected $casts = [
        'sauvegarde_auto' => 'boolean',
        'double_validation' => 'boolean',
        'journal_activites' => 'boolean',
        'multi_session' => 'boolean',
        'sig_dg_facture' => 'boolean',
        'sig_double' => 'boolean',
        'cachet_auto' => 'boolean',
        'notif_rappel' => 'boolean',
        'notif_retard' => 'boolean',
        'notif_recu' => 'boolean',
        'commission' => 'decimal:2',
        'penalite_retard' => 'decimal:2',
        'tva' => 'decimal:2',
        'aib' => 'decimal:2',
        'ras' => 'decimal:2',
        'acompte_min' => 'decimal:2',
        'seuil_dg' => 'decimal:0',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
            $model->created_by = getInfoAgent()->users->id_users ?? 'system';
        });

        static::updating(function ($model) {
            $model->updated_by = getInfoAgent()->users->id_users ?? 'system' ;
        });

    }

    /**
     * Relation avec l'agence
     */
    public function agence()
    {
        return $this->belongsTo(Agence::class, 'agence_id', 'agence_id');
    }



    /**
     * Récupérer les paramètres par agence ou créer par défaut
     */
    public static function getForAgence($agenceId)
    {
        return self::firstOrCreate(
            ['agence_id' => $agenceId],
            self::defaultValues()
        );
    }



    /**
     * Valeurs par défaut des paramètres
     */
    public static function defaultValues(): array
    {
        return [
            'devise' => 'XOF',
            'langue' => 'fr',
            'format_date' => 'd/m/Y',
            'timezone' => 'Africa/Abidjan',
            'sauvegarde_auto' => true,
            'double_validation' => true,
            'journal_activites' => true,
            'multi_session' => false,
            'periode_facturation' => 'mensuelle',
            'jour_emission' => '1',
            'delai_paiement' => 30,
            'penalite_retard' => 1.5,
            'prefixe_facture' => 'FAC-',
            'sequence_facture' => 1,
            'commission' => 15.00,
            'base_commission' => 'ht',
            'tva' => 18.00,
            'aib' => 5.00,
            'ras' => 2.00,
            'acompte_min' => 30.00,
            'mode_reglement_id' => 1,
            'logo_position' => 'gauche',
            'logo_largeur' => 200,
            'dg_titre' => 'Directeur Général',
            'sg_titre' => 'Secrétaire Général(e)',
            'cpt_titre' => 'Responsable Comptable',
            'sig_dg_facture' => true,
            'sig_double' => true,
            'cachet_auto' => false,
            'notif_rappel' => true,
            'notif_retard' => true,
            'notif_recu' => false,
            'delai_rappel' => 7,
            'seuil_dg' => 1000000,
        ];
    }

    /**
     * Obtenir le prochain numéro de facture et incrémenter
     */
    public function getNextFactureNumber(): string
    {
        $numero = str_pad($this->sequence_facture, 6, '0', STR_PAD_LEFT);
        $this->increment('sequence_facture');
        return $this->prefixe_facture . $numero;
    }
}