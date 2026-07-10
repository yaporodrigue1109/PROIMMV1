<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConfigurationTarifDuree extends Model
{
    protected $table = 'configuration_tarif_durees';

    protected $fillable = [
        'tarif_id',
        'nombre_mois',
        'prix_reduit',
    ];

    protected $casts = [
        'nombre_mois' => 'integer',
        'prix_reduit' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relation avec le tarif parent
     */
    public function tarif()
    {
        return $this->belongsTo(ConfigurationTarif::class, 'tarif_id');
    }

    /**
     * Obtenir le label formaté de la durée
     */
    public function getLabel(): string
    {
        return match ($this->nombre_mois) {
            1 => '1 mois',
            3 => '3 mois',
            6 => '6 mois',
            12 => '12 mois (1 an)',
            24 => '24 mois (2 ans)',
            36 => '36 mois (3 ans)',
            default => "{$this->nombre_mois} mois",
        };
    }

    /**
     * Calculer le prix total pour cette durée
     */
    public function getPrixTotal(): float
    {
        $prixUnitaire = $this->prix_reduit ?? $this->tarif->plan_prix_mensuel;
        return $prixUnitaire * $this->nombre_mois;
    }
}