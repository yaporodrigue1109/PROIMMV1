<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Region extends Model
{
    protected  $guarded= [];

    protected  $table= 'regions';

    public function pays()
    {
        return $this->belongsTo(Pays::class, 'pays_id');
    }

    public function villes()
    {
        return $this->hasMany(Ville::class, 'region_id');
    }
}
