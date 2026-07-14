<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class FonctionMaintenance extends Model
{
    protected $table = 'fonction_maintenance';
    protected $primaryKey = 'fonction_maintenance_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'agence_id',
        'name',
        'description',
        'categorie',
        'created_by',
        'updated_by'
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

    public function maintenanciers()
    {
        return $this->hasMany(Maintenancier::class, 'fonction_maintenance_id');
    }
}