<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CaisseSession extends Model
{
    protected $primaryKey = 'caisse_session_id';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $guarded = [];

    protected $casts = [
        'solde_ouverture' => 'decimal:2',
        'solde_theorique' => 'decimal:2',
        'solde_fermeture' => 'decimal:2',
        'ecart' => 'decimal:2',
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $session) {
            $session->caisse_session_id ??= (string) Str::uuid();
        });
    }

    public function opener()
    {
        return $this->belongsTo(User::class, 'opened_by', 'id_users');
    }

    public function closer()
    {
        return $this->belongsTo(User::class, 'closed_by', 'id_users');
    }
}
