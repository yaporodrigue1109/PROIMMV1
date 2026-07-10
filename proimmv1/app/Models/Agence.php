<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Agence extends Model
{
    use SoftDeletes;

    protected $primaryKey = 'agence_id';
    public $incrementing  = false;
    protected $keyType    = 'string';

    protected $guarded    = [];

//    protected $fillable = [
//        'agence_id',
//        'code_agence',
//        'name',
//        'adresse',
//        'tel1',
//        'tel2',
//        'email1',
//        'email2',
//        'region_id',
//        'ville_id',
//        'statut',
//        'is_principale',
//        'parent_id',
//        'responsable_id',
//        'abonnement_id',
//        'abonnement_start',
//        'abonnement_end',
//        'duree_mois',
//        'logo',
//        'signature_responsable',
//        'signature_comptabilite',
//        'signature_marketing',
//        'created_by',
//        'updated_by',
//        'deleted_by',
//    ];

    protected $casts = [
        'is_principale'   => 'boolean',
        'abonnement_start' => 'date',
        'abonnement_end'   => 'date',
    ];

    // ─── Boot ────────────────────────────────────────────────────────────────

    protected static function booted()
    {
        static::creating(function ($agence) {
            $agence->created_by = getInfoAdmin()->admin->id_admin ?? "";

            if (empty($agence->{$agence->getKeyName()})) {
                $agence->{$agence->getKeyName()} = (string) Str::uuid();
            }

        });

        static::updating(function ($agence) {
            $agence->updated_by = getInfoAdmin()->admin->id_admin ?? "";
        });

        static::deleting(function ($agence) {
            if ($agence->isForceDeleting()) {
                // Supprimer les fichiers (logo, signatures)
                Storage::disk('public')->delete($agence->logo);
            }
            $agence->deleted_by = getInfoAdmin()->admin->id_admin ?? "";
            $agence->save();
        });
    }

    public static function scopeWithDefaultRelations(): \Illuminate\Database\Eloquent\Builder
    {
        return static::with([
            'createur',
            'abonnement',
            'responsable',
            'ville',
            'region',
            'parametrage'
        ]);
    }

    public function parametrage(): HasOne
    {
        return $this->hasOne(ParametrageAgence::class, 'agence_id', 'agence_id');
    }

    // ─── Relations ───────────────────────────────────────────────────────────

    public function region(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Region::class, 'region_id');
    }

    public function ville(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Ville::class, 'ville_id');
    }

    public function responsable(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsable_id', 'id_users');
    }

    public function abonnement(): BelongsTo
    {
        return $this->belongsTo(Abonnement::class, 'abonnement_id', 'abonnement_id');
    }

    public function abonnementHistoriques(): HasMany
    {
        return $this->hasMany(AbonnementHistorique::class, 'agence_id', 'agence_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'agence_id', 'agence_id');
    }

    public function createur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'id_users');
    }

    public function updateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by', 'id_users');
    }

    // ─── Scopes ──────────────────────────────────────────────────────────────

    public function scopeActives($query)
    {
        return $query->where('statut', 'active');
    }

    public function scopeEnDemo($query)
    {
        return $query->where('statut', 'en_demo');
    }

    // ─── Accessors ───────────────────────────────────────────────────────────

    public function getStatutLibelleAttribute(): string
    {
        return match ($this->statut) {
            'active'    => 'Active',
            'en_demo'   => 'En démo',
            'desactive' => 'Désactivée',
            default     => ucfirst($this->statut),
        };
    }

    public function getAbonnementExpireAttribute(): bool
    {
        return $this->abonnement_end && $this->abonnement_end->isPast();
    }

    public function getJoursRestantsAbonnementAttribute(): int
    {
        if (!$this->abonnement_end) {
            return 0;
        }
        return max(0, now()->diffInDays($this->abonnement_end, false));
    }
}






//
//namespace App\Models;
//
//use Illuminate\Database\Eloquent\Model;
//use Illuminate\Database\Eloquent\Factories\HasFactory;
//use Illuminate\Database\Eloquent\SoftDeletes;
//use Illuminate\Foundation\Auth\User as Authenticatable;
//use Illuminate\Support\Str;
//
//
//
//// app/Models/Agence.php
//class Agence extends Model
//{
//    use HasFactory, SoftDeletes;
//
//    protected $table = 'agences';
//    protected $primaryKey = 'agence_id';
//
//    public $incrementing = false;
//    protected $keyType = 'string';
//
//    protected $fillable = [
//        'name', 'adresse', 'tel1', 'tel2', 'email1', 'email2',
//        'region_id', 'ville_id', 'statut', 'is_principale',
//        'responsable_id', 'abonnement_id', 'abonnement_start', 'abonnement_end',
//        'logo', 'signature_responsable', 'signature_comptabilite',
//        'signature_marketing', 'created_by', 'updated_by', 'deleted_by'
//    ];
//
//    protected $casts = [
//        'is_principale' => 'boolean',
//        'abonnement_start' => 'date',
//        'abonnement_end' => 'date',
//        'created_at' => 'datetime',
//        'updated_at' => 'datetime',
//        'deleted_at' => 'datetime',
//    ];
//
//    // Relations
//    public function region()
//    {
//        return $this->belongsTo(Region::class, 'region_id');
//    }
//
//    public function ville()
//    {
//        return $this->belongsTo(Ville::class, 'ville_id');
//    }
//
//    public function responsable()
//    {
//        return $this->belongsTo(User::class, 'responsable_id','id_users');
//    }
//
//    public function abonnement()
//    {
//        return $this->belongsTo(Abonnement::class, 'abonnement_id');
//    }
//
//    public function createur()
//    {
//        return $this->belongsTo(User::class, 'created_by','id_users');
//    }
//
//    // Scopes
//    public function scopeActive($query)
//    {
//        return $query->where('statut', 'active')
//            ->where('abonnement_end', '>', now());
//    }
//
//    public function scopePrincipale($query)
//    {
//        return $query->where('is_principale', true);
//    }
//
//    // Accessors
//    public function getIsAbonnementValideAttribute(): bool
//    {
//        return $this->statut === 'active' &&
//            $this->abonnement_end &&
//            $this->abonnement_end->isFuture();
//    }
//
//    // Event listeners
//    protected static function booted()
//    {
//        static::creating(function ($agence) {
//            $agence->created_by = getInfoAdmin()->admin->id_admin ?? "";
//
//            if (empty($admin->{$agence->getKeyName()})) {
//                $agence->{$agence->getKeyName()} = (string) Str::uuid();
//            }
//
//        });
//
//        static::updating(function ($agence) {
//            $agence->updated_by = getInfoAdmin()->admin->id_admin ?? "";
//        });
//
//        static::deleting(function ($agence) {
//            if ($agence->isForceDeleting()) {
//                // Supprimer les fichiers (logo, signatures)
//                Storage::disk('public')->delete($agence->logo);
//            }
//            $agence->deleted_by = getInfoAdmin()->admin->id_admin ?? "";
//            $agence->save();
//        });
//    }
//
//    public static function withDefaultRelations(): \Illuminate\Database\Eloquent\Builder
//    {
//        return static::with([
//            'createur',
//            'abonnement',
//            'responsable',
//            'ville',
//            'region'
//        ]);
//    }
//}
