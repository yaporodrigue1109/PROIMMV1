<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TypePorte extends Model
{
    use HasFactory;

    protected $table = 'type_porte';
    protected $primaryKey = 'type_porte_id';

    public $timestamps = false;

    protected $fillable = [
        'libelle',
        'description',
    ];

    // ─── Relations ────────────────────────────────────────────────

    public function portes()
    {
        return $this->hasMany(Porte::class, 'type_porte_id');
    }
}