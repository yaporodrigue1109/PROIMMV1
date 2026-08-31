<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Role extends Model
{
    use HasFactory;

    protected $table = 'roles';
    protected $primaryKey = 'role_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'agence_id',
        'base_role_id',
        'is_active',
        'is_system',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_system' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (Role $role): void {
            if (empty($role->{$role->getKeyName()})) {
                $role->{$role->getKeyName()} = (string) Str::uuid();
            }

            if (empty($role->slug) && !empty($role->name)) {
                $role->slug = Str::slug($role->name);
            }
        });
    }

    public function agence(): BelongsTo
    {
        return $this->belongsTo(Agence::class, 'agence_id', 'agence_id');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'role_id', 'role_id');
    }

    public function baseRole(): BelongsTo
    {
        return $this->belongsTo(self::class, 'base_role_id', 'role_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'id_users');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by', 'id_users');
    }

    public function rolePermissions(): HasMany
    {
        return $this->hasMany(RolePermission::class, 'role_id', 'role_id');
    }

    public function allowedPermissions(): HasMany
    {
        return $this->rolePermissions()->where('is_allowed', true);
    }

    public function hasPermission(string $moduleSlug, string $actionSlug): bool
    {
        if ($this->grantsAllPermissions()) {
            return true;
        }

        if ($this->isResponsable() && $moduleSlug === 'parametrage') {
            return true;
        }

        if (!$this->is_active) {
            return false;
        }

        return $this->allowedPermissions()
            ->whereHas('module', function ($query) use ($moduleSlug): void {
                $query->where('slug', $moduleSlug)->where('is_active', true);
            })
            ->whereHas('moduleAction', function ($query) use ($actionSlug): void {
                $query->where('slug', $actionSlug)->where('is_active', true);
            })
            ->exists();
    }

    public function grantsAllPermissions(): bool
    {
        return $this->isSuperAdmin()
            || ($this->isResponsable() && empty($this->agence_id));
    }

    public function isAdmin(): bool
    {
        return in_array($this->normalizedSlug(), ['admin', 'super-admin'], true);
    }

    public function isSuperAdmin(): bool
    {
        return $this->normalizedSlug() === 'super-admin';
    }

    public function isClient(): bool
    {
        return $this->normalizedSlug() === 'client';
    }

    public function isManager(): bool
    {
        return in_array($this->normalizedSlug(), ['manager', 'responsable', 'role-responsable'], true);
    }

    public function isResponsable(): bool
    {
        return $this->base_role_id === 'role-responsable'
            || in_array($this->normalizedSlug(), ['responsable', 'role-responsable'], true);
    }

    public function canManageUsers(): bool
    {
        return $this->isAdmin() || $this->isManager();
    }

    public function activate(): bool
    {
        return $this->update(['is_active' => true]);
    }

    public function deactivate(): bool
    {
        return $this->update(['is_active' => false]);
    }

    public function getStatusLabelAttribute(): string
    {
        return $this->is_active ? 'Actif' : 'Inactif';
    }

    public function getNameUpperCaseAttribute(): string
    {
        return Str::upper((string) $this->name);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    public function scopeForAgence($query, ?string $agenceId)
    {
        return $query->where(function ($roleQuery) use ($agenceId): void {
            $roleQuery->where('agence_id', $agenceId)
                ->orWhereNull('agence_id')
                ->orWhere('agence_id', '');
        });
    }

    public function scopeWithoutSuperAdmin($query)
    {
        return $query->whereNotIn('slug', ['super-admin', 'super_admin']);
    }

    public function scopeWithoutAdmin($query)
    {
        return $query->whereNotIn('slug', ['admin', 'super-admin', 'super_admin']);
    }

    private function normalizedSlug(): string
    {
        return Str::slug((string) ($this->slug ?: $this->name));
    }
}
