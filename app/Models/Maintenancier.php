<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Maintenancier extends Model
{
    protected $table = 'maintenanciers';
    protected $primaryKey = 'maintenancier_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'fonction_maintenance_id',
        'agence_id',
        'name',
        'entreprise',
        'tel1',
        'tel2',
        'email',
        'statut',
        'adresse',
        'type_piece_id',
        'numero_piece',
        'date_validite_piece',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'date_validite_piece' => 'date',
        'statut' => 'boolean',
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

    public function fonction()
    {
        return $this->belongsTo(FonctionMaintenance::class, 'fonction_maintenance_id');
    }

    public function agence()
    {
        return $this->belongsTo(Agence::class, 'agence_id');
    }

    public function typePiece()
    {
        return $this->belongsTo(TypePiece::class, 'type_piece_id', 'type_pieces_id');
    }

    public function maintenances()
    {
        return $this->hasMany(MaintenanceDetail::class, 'maintenancier_id');
    }

    public function scopeActif($query)
    {
        return $query->where('statut', true);
    }

    public static function withDefaultRelations(): \Illuminate\Database\Eloquent\Builder
    {
        return static::with([
            'maintenances',
            'agence',
            'fonction',
            'typePiece',

        ]);
    }
}
