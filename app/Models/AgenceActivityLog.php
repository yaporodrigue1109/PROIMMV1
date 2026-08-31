<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class AgenceActivityLog extends Model
{
    use HasUuids;

    protected $table = 'agence_activity_logs';
    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'context' => 'array',
        'created_at' => 'datetime',
    ];
}
