<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Proprietaire extends Authenticatable
{
    use HasFactory;

    protected $table = 'proprietaires';
    protected $primaryKey = 'proprietaire_id';
    public $incrementing  = false;
    protected $keyType    = 'string';

//    protected $fillable = [
//        'code',
//        'name',
//        'tel1',
//        'tel2',
//        'type_piece_id',
//        'numpiece',
//        'email',
//        'profession',
//        'nationalite',
//        'date_naiss',
//        'lieu_naiss',
//        'region_id',
//        'ville_id',
//        'adresse',
//        'photo',
//        'password',
//        'created_by',
//        'updated_by',
//        'deleted_by',
//    ];

protected $guarded   = [];

    protected $hidden = [
        'password',
    ];


    protected $casts = [
        'date_naiss' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function ($proprietaire) {
        $proprietaire->password = Hash::make("123456789");


            if (empty($proprietaire->{$proprietaire->getKeyName()})) {
                $proprietaire->{$proprietaire->getKeyName()} = (string) Str::uuid();
            }

        });

        static::updating(function ($proprietaire) {
        //    $proprietaire->updated_by = getInfoAgent()->users->id_users ?? getInfoAdmin()->admin->id_admin ;
        });

        static::deleting(function ($proprietaire) {
         //   $proprietaire->deleted_by = getInfoAgent()->users->id_users ?? getInfoAdmin()->admin->id_admin;
         //   $proprietaire->saveQuietly(); // ✅ saveQuietly pour ne pas déclencher les events
        });
    }
    // Relations
    public function agences()
    {
        return $this->hasMany(ProprietaireAgence::class, 'proprietaire_id', 'proprietaire_id');
    }

    public function proprietes()
    {
        return $this->hasMany(Propriete::class, 'proprietaire_id', 'proprietaire_id');
    }

    public function lots()
    {
        return $this->hasMany(ProprietaireLot::class, 'proprietaire_id', 'proprietaire_id');
    }

    public function region()
    {
        return $this->belongsTo(Region::class, 'region_id');
    }

    public function genre()
    {
        return $this->belongsTo(Genre::class, 'genre_id');
    }
    public function typePiece()
    {
        return $this->belongsTo(TypePiece::class, 'type_pieces_id', 'type_pieces_id');
    }

    public function ville()
    {
        return $this->belongsTo(Ville::class, 'ville_id');
    }

    public static function withDefaultRelations(): \Illuminate\Database\Eloquent\Builder
    {
        return static::with([
            'typePiece',
            'genre',
            'agences',
            'ville',
            'region'
        ]);
    }
    protected function generateCodeAgence(): string
    {
        $year = now()->year;
        $last = Proprietaire::whereYear('created_at', $year)->count() + 1;
        return sprintf("AG-%d-%04d", $year, $last);
    }
}
