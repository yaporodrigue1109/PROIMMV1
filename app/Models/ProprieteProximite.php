<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class ProprieteProximite extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'propriete_proximites';
    protected $primaryKey = 'propriete_proximite_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'propriete_proximite_id',
        'propriete_id',
        'proximite_id',
        'distance',
        'unite',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'distance' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($item) {
            if (empty($item->{$item->getKeyName()})) {
                $item->{$item->getKeyName()} = (string) Str::uuid();
            }
            $item->created_by = getInfoAgent()->users->id_users ?? getInfoAdmin()->admin->id_admin;
        });

        static::updating(function ($item) {
            $item->updated_by = getInfoAgent()->users->id_users ?? getInfoAdmin()->admin->id_admin;
        });

        static::deleting(function ($item) {
            $item->deleted_by = getInfoAgent()->users->id_users ?? null;
            $item->saveQuietly();
        });
    }

    public function propriete()
    {
        return $this->belongsTo(Propriete::class, 'propriete_id', 'propriete_id');
    }

    public function proximite()
    {
        return $this->belongsTo(ProssimitePropriete::class, 'proximite_id');
    }
}
