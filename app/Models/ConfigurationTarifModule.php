<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConfigurationTarifModule extends Model
{
    protected $table = 'configuration_tarif_modules';

    protected $fillable = [
        'tarif_id',
        'label',
        'prix_mensuel',
        'actif',
        'ordre',
    ];

    protected $casts = [
        'prix_mensuel' => 'decimal:2',
        'actif' => 'boolean',
        'ordre' => 'integer',
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
     * Scope pour les modules actifs
     */
    public function scopeActif($query)
    {
        return $query->where('actif', true);
    }

    /**
     * Scope pour ordonner les modules
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('ordre')->orderBy('created_at');
    }
}