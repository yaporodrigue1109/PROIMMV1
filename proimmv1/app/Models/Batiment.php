<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Batiment extends Model
{
    use HasFactory;

    protected $table = 'batiment';
    protected $primaryKey = 'batiment_id';
    public $incrementing  = false;
    protected $keyType    = 'string';
    protected $fillable = [
        'propriete_id',
        'agence_id',
        'name',
        'description',
        'nbre_etages',
    ];

    protected $casts = [
        'nbre_etages' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($batiment) {
            if (empty($batiment->{$batiment->getKeyName()})) {
                $batiment->{$batiment->getKeyName()} = (string) Str::uuid();
            }
            $batiment->created_by = getInfoAgent()->users->id_users ?? getInfoAdmin()->admin->id_admin ;
        });

        static::updating(function ($batiment) {
            $batiment->updated_by = getInfoAgent()->users->id_users ?? getInfoAdmin()->admin->id_admin ;
        });

    }

    // ─── Relations ────────────────────────────────────────────────

    public function propriete()
    {
        return $this->belongsTo(Propriete::class, 'propriete_id');
    }

    public function portes()
    {
        return $this->hasMany(Porte::class, 'batiment_id');
    }

    public function portesLibres()
    {
        return $this->hasMany(Porte::class, 'batiment_id')->where('is_occupe', false)->where('is_actif', true);
    }

    // ─── Accesseurs ───────────────────────────────────────────────

    public function getNbrePorteTotalAttribute(): int
    {
        return $this->portes->count();
    }

    public function getNbrePorteLibreAttribute(): int
    {
        return $this->portes->where('is_occupe', false)->count();
    }
}