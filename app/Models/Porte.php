<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Porte extends Model
{
    use HasFactory;

    protected $table = 'porte';
    protected $primaryKey = 'porte_id';
    public $incrementing  = false;
    protected $keyType    = 'string';
    protected $fillable = [
        'batiment_id',
        'type_porte_id',
        'numero_porte',
        'agence_id',
        'superficie_m2',
        'etage',
        'is_allocation',
        'description',
        'is_occupe',
        'is_actif',
        'caution',
        'avance',
        'agence',
        'mt_caution_cie',
        'mt_caution_sodeci',
        'mt_autre_frais',
        'mt_loyer',
        'equipements',
    ];

    protected $casts = [
        'superficie_m2' => 'integer',
        'etage'         => 'integer',
        'is_allocation' => 'boolean',
        'is_occupe'     => 'boolean',
        'is_actif'      => 'boolean',
    ];

    // ─── Relations ────────────────────────────────────────────────
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($porte) {
            if (empty($porte->{$porte->getKeyName()})) {
                $porte->{$porte->getKeyName()} = (string) Str::uuid();
            }
            $porte->created_by = getInfoAgent()->users->id_users ?? getInfoAdmin()->admin->id_admin ;
        });

        static::updating(function ($porte) {
            $porte->updated_by = getInfoAgent()->users->id_users ?? getInfoAdmin()->admin->id_admin ;
        });

    }
    public function batiment()
    {
        return $this->belongsTo(Batiment::class, 'batiment_id');
    }

    public function typePorte()
    {
        return $this->belongsTo(TypePorte::class, 'type_porte_id');
    }

    public function tarifs()
    {
        return $this->hasMany(TarifPorte::class, 'porte_id')->orderByDesc('date_effet');
    }

    public function loyers()
    {
        return $this->hasMany(Loyer::class, 'porte_id');
    }
         public function locatairesAgence()
    {
        return $this->hasMany(LocataireAgence::class, 'porte_id', 'porte_id');
    }

    public function tarifActif()
    {
        return $this->hasOne(TarifPorte::class, 'porte_id')->where('is_actif', true);
    }

    // ─── Scopes ───────────────────────────────────────────────────

    public function scopeLibre($query)
    {
        return $query->where('is_occupe', false)->where('is_actif', true);
    }

    public function scopeOccupe($query)
    {
        return $query->where('is_occupe', true);
    }

    public function scopeActif($query)
    {
        return $query->where('is_actif', true);
    }
   
}
