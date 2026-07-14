<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $table = 'users';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = 'id_users';

    protected $fillable = [
        'name', 'email', 'password', 'adresse', 'agence_id',
        'is_responsable', 'role_id', 'tel1', 'tel2', 'statut',
        'photo', 'created_by', 'updated_by', 'deleted_by',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_responsable'    => 'boolean',
        'created_at'        => 'datetime',
        'updated_at'        => 'datetime',
        'deleted_at'        => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($users) {
            $users->created_by = getInfoAgent()->users->id_users ?? "";
            if (empty($users->{$users->getKeyName()})) {
                $users->{$users->getKeyName()} = (string) Str::uuid();
            }
        });

        static::updating(function ($users) {
            $users->updated_by = getInfoAgent()->users->id_users ?? "";

        });
    }

    // =============================================
    // PERMISSIONS — ajoutées depuis Admin
    // =============================================

    /**
     * Vérifier si l'utilisateur a une permission spécifique
     */
    public function hasPermission($permissions, $requireAll = false): bool
    {
        if (is_string($permissions)) {
            $permissions = [$permissions];
        }

        $userPermissions = $this->getPermissions();

        if ($requireAll) {
            foreach ($permissions as $permission) {
                if (!in_array($permission, $userPermissions)) {
                    return false;
                }
            }
            return true;
        } else {
            foreach ($permissions as $permission) {
                if (in_array($permission, $userPermissions)) {
                    return true;
                }
            }
            return false;
        }
    }

    /**
     * Récupérer toutes les permissions de l'utilisateur
     */
    public function getPermissions(): array
    {
        $rolePermissions     = $this->getRolePermissions();
        $specificPermissions = $this->getSpecificPermissions();

        return array_unique(array_merge($rolePermissions, $specificPermissions));
    }

    /**
     * Obtenir les permissions selon le rôle (role_id)
     * Adapté pour User qui utilise role_id au lieu de role
     */
    protected function getRolePermissions(): array
    {
        $roleKey = $this->role?->role_id ?? null;
        $roleSlug = Str::slug((string) ($this->role?->name ?? ''), '-');

        $permissions = [
            'role-responsable' => [
                'view_proprietes',
                'create_proprietes',
                'edit_proprietes',
                'view_contrats',
                'create_contrats',
                'edit_contrats',
                'view_locataires',
                'create_locataires',
                'edit_locataires',
                'view_proprietaires',
                'create_proprietaires',
                'edit_proprietaires',
                'view_rapports',
                'export_rapports',
                'view_caisse',
                'view_loyer',
                'view_reversement',
                'manage_personnel',
            ],
            'responsable' => [
                'view_proprietes',
                'create_proprietes',
                'edit_proprietes',
                'view_contrats',
                'create_contrats',
                'edit_contrats',
                'view_locataires',
                'create_locataires',
                'edit_locataires',
                'view_proprietaires',
                'create_proprietaires',
                'edit_proprietaires',
                'view_rapports',
                'export_rapports',
                'view_caisse',
                'view_loyer',
                'view_reversement',
                'manage_personnel',
            ],
            'role-agent' => [
                'view_proprietes',
                'create_proprietes',
                'edit_proprietes',
                'view_contrats',
                'create_contrats',
                'view_locataires',
                'create_locataires',
                'view_proprietaires',
                'view_rapports',
                'view_caisse',
                'view_loyer',
            ],
            'agent' => [
                'view_proprietes',
                'create_proprietes',
                'edit_proprietes',
                'view_contrats',
                'create_contrats',
                'view_locataires',
                'create_locataires',
                'view_proprietaires',
                'view_rapports',
                'view_caisse',
                'view_loyer',
            ],
            'role-comptable' => [
                'view_contrats',
                'view_rapports',
                'export_rapports',
                'view_caisse',
                'view_loyer',
                'view_reversement',
            ],
            'comptable' => [
                'view_contrats',
                'view_rapports',
                'export_rapports',
                'view_caisse',
                'view_loyer',
                'view_reversement',
            ],
            'role-technicien' => [],
            'technicien' => [],
        ];

        return $permissions[$roleKey] ?? $permissions[$roleSlug] ?? [];
    }

    /**
     * Récupérer les permissions spécifiques (depuis une table si besoin)
     */
    protected function getSpecificPermissions(): array
    {
        return [];
    }

    /**
     * Vérifier si l'utilisateur a un rôle spécifique
     */
    public function hasRole($roles, $requireAll = false): bool
    {
        if (is_string($roles)) {
            $roles = [$roles];
        }

        $roleName = $this->role?->name ?? null;

        if ($requireAll) {
            foreach ($roles as $role) {
                if ($roleName !== $role) return false;
            }
            return true;
        } else {
            foreach ($roles as $role) {
                if ($roleName === $role) return true;
            }
            return false;
        }
    }

    /**
     * Vérifier si l'utilisateur peut accéder à une agence spécifique
     */
    public function canAccessAgence(string $agenceId): bool
    {
        // Le responsable ne peut accéder qu'à son agence
        if ($this->isResponsable() && $this->agence_id === $agenceId) {
            return true;
        }

        return false;
    }

    // =============================================
    // RELATIONS
    // =============================================

    public function agence()
    {
        return $this->belongsTo(Agence::class, 'agence_id');
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
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
    // MÉTHODES EXISTANTES
    // =============================================

    public function isResponsable(): bool
    {
        return $this->is_responsable === true;
    }

    public function isActive(): bool
    {
        return $this->statut === 'actif';
    }

    public function isInactive(): bool
    {
        return $this->statut === 'inactif';
    }

    public function isSuspended(): bool
    {
        return $this->statut === 'suspendu';
    }

    public function getFullName(): string
    {
        return $this->name;
    }

    // =============================================
    // SCOPES
    // =============================================

    public function scopeActive($query)       { return $query->where('statut', 'actif'); }
    public function scopeInactive($query)     { return $query->where('statut', 'inactif'); }
    public function scopeSuspended($query)    { return $query->where('statut', 'suspendu'); }
    public function scopeResponsable($query)  { return $query->where('is_responsable', true); }
    public function scopeByAgence($query, $agenceId) { return $query->where('agence_id', $agenceId); }
    public function scopeByRole($query, $roleId)     { return $query->where('role_id', $roleId); }
    public function scopeSearch($query, $term)
    {
        return $query->where('name', 'like', "%{$term}%")
            ->orWhere('email', 'like', "%{$term}%")
            ->orWhere('tel1', 'like', "%{$term}%");
    }
}
