<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class MaintenanceDetail extends Model
{
    use SoftDeletes;

    protected $table = 'maintenance_detail';
    protected $primaryKey = 'maintenance_detail_id';
    public    $incrementing = false;
    protected $keyType    = 'string';
    protected $fillable = [
        'maintenance_id',
        'maintenancier_id',
        'type_intervention_id',
        'date_debut',
        'date_fin',
        'priorite',
        'montant',
        'note',
        'statut',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'montant'    => 'decimal:2',
        'date_debut' => 'date',
        'date_fin'   => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Priorités
    const PRIORITE_BASSE   = 'basse';
    const PRIORITE_NORMALE = 'normale';
    const PRIORITE_HAUTE   = 'haute';

    // Statuts
    const STATUT_PLANIFIE   = 'en attente';
    const STATUT_EN_ATTENTE = 'en attente';
    const STATUT_EN_COURS   = 'en cours';
    const STATUT_TERMINE    = 'terminer';
    const STATUT_ANNULE     = 'annuler';

    // -------------------------------------------------------------------------
    // Relations
    // -------------------------------------------------------------------------
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

        static::deleting(function ($model) {
            $model->deleted_by = getInfoAgent()?->users?->id_users
                ?? getInfoAdmin()?->admin?->id_admin
                ?? 'system';
            $model->deleted_at = now();
        });

    }
    public function maintenance(): BelongsTo
    {
        return $this->belongsTo(Maintenance::class, 'maintenance_id', 'maintenance_id');
    }

    public function maintenancier(): BelongsTo
    {
        return $this->belongsTo(Maintenancier::class, 'maintenancier_id', 'maintenancier_id');
    }

    public function typeIntervention(): BelongsTo
    {
        return $this->belongsTo(TypeMaintenance::class, 'type_intervention_id', 'type_maintenance_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
