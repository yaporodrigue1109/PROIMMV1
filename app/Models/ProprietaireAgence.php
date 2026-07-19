<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ProprietaireAgence extends Model
{
    use HasFactory, SoftDeletes;

    protected $table      = 'proprietaire_agences';
    protected $primaryKey = 'proprietaire_agence_id';
    public $incrementing  = false;
    protected $keyType    = 'string';

    protected $fillable = [
        'proprietaire_agence_id',
        'proprietaire_id',
        'agence_id',
        'is_active',
        'date_activation',
        'date_desactivation',
        'agent_activation_id',
        'agent_desactivation_id',
        'name_representant',
        'adresse_representant',
        'tel1_representant',
        'tel2_representant',
        'email_representant',
        'genre_representant_id',
        'type_pieces_representant_id',
        'numpiece_representant',
        'photo_representant',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'is_active'           => 'boolean',
        'date_activation'     => 'datetime',
        'date_desactivation'  => 'datetime',
        'created_at'          => 'datetime',
        'updated_at'          => 'datetime',
        'deleted_at'          => 'datetime',
    ];

    protected static function boot()
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

        static::deleting(function ($model) {
            $model->deleted_by = getInfoAgent()->users->id_users ?? getInfoAdmin()->admin->id_admin;
            $model->saveQuietly(); // ✅ saveQuietly pour ne pas déclencher les events
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

    public function agentActivation()
    {
        return $this->belongsTo(User::class, 'agent_activation_id', 'id_users');
    }

    public function agentDesactivation()
    {
        return $this->belongsTo(User::class, 'agent_desactivation_id', 'id_users');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'id_users');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by', 'id_users');
    }

    public function deleter()
    {
        return $this->belongsTo(User::class, 'deleted_by', 'id_users');
    }

    // =============================================
    // SCOPES
    // =============================================

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    public function scopeByAgence($query, $agenceId)
    {
        return $query->where('agence_id', $agenceId);
    }

    public function scopeByProprietaire($query, $proprietaireId)
    {
        return $query->where('proprietaire_id', $proprietaireId);
    }

    // =============================================
    // MÉTHODES
    // =============================================

    public function isActive(): bool
    {
        return $this->is_active === true;
    }

    public function activate(string $agentId): void
    {
        $this->update([
            'is_active'          => true,
            'date_activation'    => now(),
            'agent_activation_id' => $agentId,
            'date_desactivation' => null,
            'agent_desactivation_id' => null,
        ]);
    }

    public function deactivate(string $agentId): void
    {
        $this->update([
            'is_active'             => false,
            'date_desactivation'    => now(),
            'agent_desactivation_id' => $agentId,
        ]);
    }
}
