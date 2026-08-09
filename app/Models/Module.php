<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Module extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'modules';
    protected $primaryKey = 'module_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'name',
        'slug',
        'icon',
        'route',
        'parent_id',
        'order_index',
        'is_active',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'order_index' => 'integer',
        'is_active' => 'boolean',
        'deleted_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (Module $module): void {
            if (empty($module->{$module->getKeyName()})) {
                $module->{$module->getKeyName()} = (string) Str::uuid();
            }

            if (empty($module->slug) && !empty($module->name)) {
                $module->slug = Str::slug($module->name);
            }
        });
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id', 'module_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id', 'module_id')
            ->orderBy('order_index');
    }

    public function activeChildren(): HasMany
    {
        return $this->children()->where('is_active', true);
    }

    public function actions(): HasMany
    {
        return $this->hasMany(ModuleAction::class, 'module_id', 'module_id')
            ->orderBy('order_index');
    }

    public function activeActions(): HasMany
    {
        return $this->actions()->where('is_active', true);
    }

    public function rolePermissions(): HasMany
    {
        return $this->hasMany(RolePermission::class, 'module_id', 'module_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeParents($query)
    {
        return $query->whereNull('parent_id');
    }
}
