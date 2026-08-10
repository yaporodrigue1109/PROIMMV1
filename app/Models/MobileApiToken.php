<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class MobileApiToken extends Model
{
    protected $primaryKey = 'mobile_api_token_id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $guarded = [];

    protected $casts = [
        'last_used_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $token) {
            $token->mobile_api_token_id ??= (string) Str::uuid();
        });
    }
}
