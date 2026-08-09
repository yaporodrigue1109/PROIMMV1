<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class ContactMessage extends Model
{
    protected $primaryKey = 'contact_message_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'request_type',
        'name',
        'email',
        'phone',
        'subject',
        'message',
        'status',
        'ip_address',
        'user_agent',
        'processed_at',
        'processed_by',
    ];

    protected $casts = [
        'processed_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (ContactMessage $message): void {
            if (empty($message->{$message->getKeyName()})) {
                $message->{$message->getKeyName()} = (string) Str::uuid();
            }
        });
    }

    public function replies(): HasMany
    {
        return $this->hasMany(ContactReply::class, 'contact_message_id', 'contact_message_id')->oldest();
    }
}
