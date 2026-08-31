<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class AgencyAnnouncement extends Model
{
    protected $primaryKey = 'announcement_id';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $guarded = [];
    protected $casts = ['published_at' => 'datetime'];

    protected static function booted(): void
    {
        static::creating(fn (self $item) => $item->announcement_id ??= (string) Str::uuid());
    }

    public function recipients()
    {
        return $this->hasMany(AgencyAnnouncementRecipient::class, 'announcement_id');
    }
}
