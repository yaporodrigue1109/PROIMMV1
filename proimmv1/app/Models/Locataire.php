<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Auth\User as Authenticatable;


class Locataire extends Authenticatable
{
    use HasFactory;

    protected $table      = 'locataire';
    protected $primaryKey = 'locataire_id';
    public $incrementing  = false;
    protected $keyType    = 'string';

    protected $fillable = [
        'locataire_id',
        'name',
        'code',
        'tel1',
        'tel2',
        'email',
        'region_id',
        'ville_id',
        'adresse',
        'nationalite',
        'type_piece_id',
        'num_piece',
        'date_expiration_piece',
        'date_naissance',
        'lieu_naissance',
        'genre_id',
        'photo',
        'image_pice',
        'profession',
        'password',
    ];

    protected $hidden = ['password'];

    protected $casts = [
        'date_expiration_piece' => 'date',
        'date_naissance'        => 'date',
    ];

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
            $model->password = Hash::make("123456789");
            //$model->code = $model->code ?? 'LOC-' . strtoupper(Str::random(6));
        });
    }

    // ─── Relations ────────────────────────────────────────────────

//    /** Contrats de location dans les agences */
    public function contrats()
    {
        return $this->hasMany(LocataireAgence::class, 'locataire_id', 'locataire_id');
    }

    public function loyers()
    {
        return $this->hasMany(Loyer::class, 'locataire_id', 'locataire_id');
    }

    public function transactions()
    {
        return $this->hasMany(TransactionAgence::class, 'locataire_id', 'locataire_id');
    }


    public function region()
    {
        return $this->belongsTo(Region::class, 'region_id');
    }

    public function ville()
    {
        return $this->belongsTo(Ville::class, 'ville_id');
    }
    public function genre()
    {
        return $this->belongsTo(Genre::class, 'genre_id');
    }
    // ─── Accesseurs ───────────────────────────────────────────────

    /** Contrat actif */
    public function bauxActifs($agence_id)
    {
        return $this->hasMany(LocataireAgence::class, 'locataire_id')->where('agence_id', $agence_id)
            ->where('is_active', 1);
    }

    public function scopeSearch($query, string $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
                ->orWhere('tel1', 'like', "%{$term}%")
                ->orWhere('tel2', 'like', "%{$term}%")
                ->orWhere('code', 'like', "%{$term}%")
                ->orWhere('email', 'like', "%{$term}%")
                ->orWhere('num_piece', 'like', "%{$term}%");
        });
    }

    public static function withDefaultRelations(): \Illuminate\Database\Eloquent\Builder
    {
        return static::with([

            'ville',
            'region',
            'genre'
        ]);
    }

}