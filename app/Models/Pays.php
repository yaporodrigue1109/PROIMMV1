<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pays extends Model
{
    protected $table = 'pays';

    protected $guarded = [];

    protected $casts = ['actif' => 'boolean'];

    public function regions(): HasMany
    {
        return $this->hasMany(Region::class, 'pays_id');
    }
}
