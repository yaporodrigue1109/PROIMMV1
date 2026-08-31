<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Ville extends Model
{
    protected  $guarded= [];

    protected  $table= 'villes';

    public function region()
    {
        return $this->belongsTo(Region::class, 'region_id');
    }

    /** Compatibilité avec l'ancien nom de relation. */
    public function regions()
    {
        return $this->region();
    }

}
