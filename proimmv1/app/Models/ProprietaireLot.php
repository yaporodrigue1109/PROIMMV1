<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ProprietaireLot extends Model
{
    use HasFactory, SoftDeletes;

    protected $table      = 'propietaire_lots';
    protected $primaryKey = 'propreietaire_lot_id';
    public $incrementing  = false;
    protected $keyType    = 'string';

    protected $fillable = [
        'propreietaire_lot_id',
        'name',
        'superficie',
        'region_id',
        'ville_id',
        'adresse',
        'num_lot',
        'num_ilot',
        'proprietaire_id',
        'agence_id',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($lot) {
            if (empty($lot->{$lot->getKeyName()})) {
                $lot->{$lot->getKeyName()} = (string) Str::uuid();
            }
            $lot->created_by = getInfoAgent()->users->id_users ?? getInfoAdmin()->admin->id_admin ;
        });
        static::updating(function ($lot) {
            $lot->updated_by = getInfoAgent()->users->id_users ?? getInfoAdmin()->admin->id_admin ;
        });
        static::deleting(function ($lot) {
            $lot->deleted_by = getInfoAgent()->users->id_users ?? null;
            $lot->saveQuietly();
        });
    }

    // =============================================
    // RELATIONS
    // =============================================

    public function proprietaire()
    {
        return $this->belongsTo(Proprietaire::class, 'proprietaire_id', 'proprietaire_id');
    }

    public function agence()
    {
        return $this->belongsTo(Agence::class, 'agence_id', 'agence_id');
    }

    public function region()
    {
        return $this->belongsTo(Region::class, 'region_id');
    }

    public function ville()
    {
        return $this->belongsTo(Ville::class, 'ville_id');
    }

    public function proprietes()
    {
        return $this->hasMany(Propriete::class, 'lot_id', 'propreietaire_lot_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'id_users');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by', 'id_users');
    }

    // =============================================
    // SCOPES
    // =============================================

    public function scopeByAgence($query, string $agenceId)
    {
        return $query->where('agence_id', $agenceId);
    }

    public function scopeByProprietaire($query, string $proprietaireId)
    {
        return $query->where('proprietaire_id', $proprietaireId);
    }
}
