<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Str;

class Admin extends Authenticatable
{
    use HasFactory, SoftDeletes;

    protected $table = 'admins';
    protected $primaryKey = 'id_admin';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $is_super_admin= true;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'statut',
        'password',
        'created_by',
        'updated_by',
        'deleted_by',
        'remember_token', // Add this
    ];

    protected $hidden = [
        'password',
        'remember_token', // Add this
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'statut' => 'integer',
    ];

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    const DELETED_AT = 'deleted_at';

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($admin) {
            if (empty($admin->{$admin->getKeyName()})) {
                $admin->{$admin->getKeyName()} = (string) Str::uuid();
            }
        });
    }

    // Remove the setPasswordAttribute mutator to avoid double hashing
    // Let Laravel handle password hashing automatically

    // Instead, if you need to ensure password is always hashed:
    public function setPasswordAttribute($value)
    {
        if (!empty($value)) {
            // Only hash if not already hashed
            if (password_get_info($value)['algo'] === 0) {
                $this->attributes['password'] = bcrypt($value);
            } else {
                $this->attributes['password'] = $value;
            }
        }
    }

    // ===== RELATIONS =====
    public function creator()
    {
        return $this->belongsTo(Admin::class, 'created_by', 'id_admin');
    }

    public function updater()
    {
        return $this->belongsTo(Admin::class, 'updated_by', 'id_admin');
    }

    public function deleter()
    {
        return $this->belongsTo(Admin::class, 'deleted_by', 'id_admin');
    }

    // ===== SCOPES =====
    public function scopeActif($query)
    {
        return $query->where('statut', 1);
    }

    public function scopeInactif($query)
    {
        return $query->where('statut', 0);
    }

    // ===== MÉTHODES =====
    public function isActif()
    {
        return $this->statut === 1;
    }

    public function isInactif()
    {
        return $this->statut === 0;
    }

    // Override the getAuthPassword method to ensure correct password field
    public function getAuthPassword()
    {
        return $this->password;
    }


    /**
     * Vérifier si l'utilisateur a une permission spécifique
     *
     * @param string|array $permissions
     * @param bool $requireAll
     * @return bool
     */
    public function hasPermission($permissions, $requireAll = false): bool
    {

        // Si c'est un super admin, il a toutes les permissions
        if ($this->isSuperAdmin()) {
            return true;
        }

        // Convertir en tableau si c'est une string
        if (is_string($permissions)) {
            $permissions = [$permissions];
        }

        // Récupérer les permissions de l'utilisateur
        $userPermissions = $this->getPermissions();

        // Vérifier les permissions
        if ($requireAll) {
            // Toutes les permissions sont requises
            foreach ($permissions as $permission) {
                if (!in_array($permission, $userPermissions)) {
                    return false;
                }
            }
            return true;
        } else {
            // Au moins une permission est requise
            foreach ($permissions as $permission) {
                if (in_array($permission, $userPermissions)) {
                    return true;
                }
            }
            return false;
        }
    }

    /**
     * Vérifier si l'utilisateur est super admin
     *
     * @return bool
     */
    public function isSuperAdmin(): bool
    {

        return $this->is_super_admin === true || $this->role === 'super_admin';
    }

    /**
     * Vérifier si l'utilisateur est admin
     *
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin' || $this->isSuperAdmin();
    }

    /**
     * Vérifier si l'utilisateur est gestionnaire d'agence
     *
     * @return bool
     */
    public function isGestionnaire(): bool
    {
        return $this->role === 'gestionnaire';
    }

    /**
     * Vérifier si l'utilisateur est responsable d'agence
     *
     * @return bool
     */
    public function isResponsableAgence(): bool
    {
        return $this->role === 'responsable_agence';
    }

    /**
     * Récupérer toutes les permissions de l'utilisateur
     *
     * @return array
     */
    public function getPermissions(): array
    {
        // Si c'est un super admin, retourner toutes les permissions
        if ($this->isSuperAdmin()) {
            return $this->getAllPermissions();
        }

        // Récupérer les permissions selon le rôle
        $rolePermissions = $this->getRolePermissions();

        // Récupérer les permissions spécifiques (si vous avez une table permissions)
        $specificPermissions = $this->getSpecificPermissions();

        // Fusionner les permissions
        return array_unique(array_merge($rolePermissions, $specificPermissions));
    }

    /**
     * Obtenir toutes les permissions possibles (pour super admin)
     *
     * @return array
     */
    protected function getAllPermissions(): array
    {
        return [
            // Permissions agences
            'view_agences',
            'create_agences',
            'edit_agences',
            'delete_agences',
            'manage_abonnements',

            // Permissions utilisateurs
            'view_users',
            'create_users',
            'edit_users',
            'delete_users',

            // Permissions propriétés
            'view_proprietes',
            'create_proprietes',
            'edit_proprietes',
            'delete_proprietes',

            // Permissions contrats
            'view_contrats',
            'create_contrats',
            'edit_contrats',
            'delete_contrats',

            // Permissions rapports
            'view_rapports',
            'export_rapports',

            // Permissions paramètres
            'manage_settings',
            'manage_backups',
        ];
    }

    /**
     * Obtenir les permissions selon le rôle
     *
     * @return array
     */
    protected function getRolePermissions(): array
    {
        $permissions = [
            'admin' => [
                'view_agences',
                'create_agences',
                'edit_agences',
                'delete_agences',
                'manage_abonnements',
                'view_users',
                'create_users',
                'edit_users',
                'delete_users',
                'view_rapports',
                'export_rapports',
                'manage_settings',
            ],
            'gestionnaire' => [
                'view_agences',
                'edit_agences',
                'view_users',
                'create_users',
                'edit_users',
                'view_proprietes',
                'create_proprietes',
                'edit_proprietes',
                'view_contrats',
                'create_contrats',
                'edit_contrats',
                'view_rapports',
                'export_rapports',
            ],
            'responsable_agence' => [
                'view_proprietes',
                'create_proprietes',
                'edit_proprietes',
                'view_contrats',
                'create_contrats',
                'edit_contrats',
                'view_rapports',
            ],
            'comptable' => [
                'view_agences',
                'manage_abonnements',
                'view_contrats',
                'view_rapports',
                'export_rapports',
            ],
            'commercial' => [
                'view_proprietes',
                'create_proprietes',
                'edit_proprietes',
                'view_contrats',
                'create_contrats',
            ],
        ];

        return $permissions[$this->role] ?? [];
    }

    /**
     * Récupérer les permissions spécifiques de l'utilisateur (depuis une table)
     * Si vous avez une table user_permissions
     *
     * @return array
     */
    protected function getSpecificPermissions(): array
    {
        // Si vous avez une table user_permissions
        // return $this->permissions()->pluck('name')->toArray();

        // Pour l'instant, retourner un tableau vide
        return [];
    }

    /**
     * Relation avec les permissions spécifiques (optionnel)
     */
    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'user_permissions');
    }

    /**
     * Relation avec l'agence
     */
    public function agence()
    {
        return $this->belongsTo(Agence::class, 'agence_id', 'agence_id');
    }

    /**
     * Vérifier si l'utilisateur peut accéder à une agence spécifique
     *
     * @param int $agenceId
     * @return bool
     */
    public function canAccessAgence(int $agenceId): bool
    {
        // Super admin et admin peuvent accéder à toutes les agences
        if ($this->isAdmin()) {
            return true;
        }

        // Responsable d'agence ne peut accéder qu'à son agence
        if ($this->isResponsableAgence() && $this->agence_id === $agenceId) {
            return true;
        }

        // Gestionnaire peut accéder aux agences qu'il gère
        if ($this->isGestionnaire()) {
            // Logique supplémentaire si nécessaire
            return true;
        }

        return false;
    }

    /**
     * Vérifier si l'utilisateur a un rôle spécifique
     *
     * @param string|array $roles
     * @param bool $requireAll
     * @return bool
     */
    public function hasRole($roles, $requireAll = false): bool
    {
        if (is_string($roles)) {
            $roles = [$roles];
        }

        if ($requireAll) {
            foreach ($roles as $role) {
                if ($this->role !== $role) {
                    return false;
                }
            }
            return true;
        } else {
            foreach ($roles as $role) {
                if ($this->role === $role) {
                    return true;
                }
            }
            return false;
        }
    }

    /**
     * Vérifier si le compte est actif
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Scope pour les utilisateurs actifs
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope pour les utilisateurs par rôle
     */
    public function scopeByRole($query, $role)
    {
        return $query->where('role', $role);
    }
}