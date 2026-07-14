<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;


class Role extends Model
{
    protected  $guarded= [];

    protected  $table= 'roles';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = 'role_id';

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($role) {
            if (empty($role->{$role->getKeyName()})) {
                $role->{$role->getKeyName()} = (string) Str::uuid();
            }
        });
    }
    public function agence()
    {
        return $this->belongsTo(Agence::class, 'agence_id','agence_id');
    }
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'id_users');
    }

    /**
     * Relation avec l'utilisateur qui a mis à jour
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by', 'id_users');
    }

}