<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class RolePermission extends Model
{
    use HasFactory;

    protected $table = 'role_permissions';
    protected $primaryKey = 'role_permission_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'role_id',
        'module_id',
        'module_action_id',
        'is_allowed',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_allowed' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (RolePermission $permission): void {
            if (empty($permission->{$permission->getKeyName()})) {
                $permission->{$permission->getKeyName()} = (string) Str::uuid();
            }
        });
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id', 'role_id');
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class, 'module_id', 'module_id');
    }

    public function action(): BelongsTo
    {
        return $this->belongsTo(ModuleAction::class, 'module_action_id', 'module_action_id');
    }

    public function moduleAction(): BelongsTo
    {
        return $this->action();
    }

    public function scopeAllowed($query)
    {
        return $query->where('is_allowed', true);
    }
}
