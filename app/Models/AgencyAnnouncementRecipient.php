<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class AgencyAnnouncementRecipient extends Model
{
    protected $primaryKey = 'announcement_recipient_id';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $guarded = [];
    protected $casts = ['read_at' => 'datetime'];

    protected static function booted(): void
    {
        static::creating(fn (self $item) => $item->announcement_recipient_id ??= (string) Str::uuid());
    }

    public function announcement()
    {
        return $this->belongsTo(AgencyAnnouncement::class, 'announcement_id');
    }
}
