<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
class TypeMaintenance extends Model
{
    protected $table = 'type_maintenances';
    protected $primaryKey = 'type_maintenance_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'agence_id',
        'name',
        'categorie',
        'duree_estimee',
        'description',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'duree_estimee' => 'decimal:2',
    ];

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function ($model) {

            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
            $model->created_by = getInfoAgent()->users->id_users ?? getInfoAdmin()->admin->id_admin ;
        });

        static::updating(function ($model) {
            $model->updated_by = getInfoAgent()->users->id_users ?? getInfoAdmin()->admin->id_admin ;
        });


    }
    public function agence()
    {
        return $this->belongsTo(Agence::class, 'agence_id');
    }

    public function maintenances()
    {
        return $this->hasMany(MaintenanceDetail::class, 'type_intervention_id', 'type_maintenance_id');
    }
}
