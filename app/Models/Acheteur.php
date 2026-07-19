<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Acheteur extends Model
{
    use HasFactory;

    protected $table = 'acheteurs';
    protected $primaryKey = 'id_acheteur';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = true;

    protected $guarded = [];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

      protected static function boot(): void
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
            $model->created_at = now();
            $model->updated_at = now();
        });
    }

    // ─── Relations ────────────────────────────────────────────────

    public function agence()
    {
        return $this->belongsTo(Agence::class, 'agence_id');
    }

    public function typePiece()
    {
        return $this->belongsTo(TypePiece::class, 'type_piece_id');
    }

    public function ventes()
    {
        return $this->hasMany(VenteBien::class, 'acheteur_vente_id', 'id_acheteur');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // ─── Scopes ──────────────────────────────────────────────────

    public function scopeByAgence($query, string $agenceId)
    {
        return $query->where('agence_id', $agenceId);
    }

    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'LIKE', "%{$search}%")
              ->orWhere('telephone1', 'LIKE', "%{$search}%")
              ->orWhere('telephone2', 'LIKE', "%{$search}%")
              ->orWhere('email', 'LIKE', "%{$search}%")
              ->orWhere('numero_piece', 'LIKE', "%{$search}%");
        });
    }

    // ─── Accesseurs ──────────────────────────────────────────────

    public function getFullNameAttribute()
    {
        return $this->name;
    }

    public function getTelephonePrincipalAttribute()
    {
        return $this->telephone1;
    }

    public function getTelephoneSecondaireAttribute()
    {
        return $this->telephone2;
    }
}