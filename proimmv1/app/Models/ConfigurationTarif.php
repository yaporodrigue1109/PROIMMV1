<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ConfigurationTarif extends Model
{
    protected $table = 'configuration_tarifs';

    protected $fillable = [
        'plan_nom',
        'plan_prix_mensuel',
        'delai_grace',
        'cycle_facturation',
        'plan_description',
    ];

    protected $casts = [
        'plan_prix_mensuel' => 'decimal:2',
        'delai_grace' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relation avec les durées disponibles
     */
    public function durees()
    {
        return $this->hasMany(ConfigurationTarifDuree::class, 'tarif_id');
    }

    /**
     * Relation avec les modules
     */
    public function modules()
    {
        return $this->hasMany(ConfigurationTarifModule::class, 'tarif_id');
    }

    /**
     * Récupérer les modules actifs
     */
    public function modulesActifs()
    {
        return $this->modules()->where('actif', true);
    }
}