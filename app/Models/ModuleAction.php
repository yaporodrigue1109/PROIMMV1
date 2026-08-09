<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class ModuleAction extends Model
{
    use HasFactory;

    protected $table = 'module_actions';
    protected $primaryKey = 'module_action_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'module_id',
        'name',
        'slug',
        'description',
        'is_critical',
        'order_index',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'order_index' => 'integer',
        'is_active' => 'boolean',
        'is_critical' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (ModuleAction $action): void {
            if (empty($action->{$action->getKeyName()})) {
                $action->{$action->getKeyName()} = (string) Str::uuid();
            }

            if (empty($action->slug) && !empty($action->name)) {
                $action->slug = Str::slug($action->name);
            }
        });
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class, 'module_id', 'module_id');
    }

    public function rolePermissions(): HasMany
    {
        return $this->hasMany(RolePermission::class, 'module_action_id', 'module_action_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
