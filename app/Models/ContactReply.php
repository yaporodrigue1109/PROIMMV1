<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ContactReply extends Model
{
    protected $primaryKey = 'contact_reply_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'contact_message_id',
        'admin_id',
        'channel',
        'recipient',
        'subject',
        'message',
        'status',
        'sent_at',
        'error_message',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (ContactReply $reply): void {
            if (empty($reply->{$reply->getKeyName()})) {
                $reply->{$reply->getKeyName()} = (string) Str::uuid();
            }
        });
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(ContactMessage::class, 'contact_message_id', 'contact_message_id');
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'admin_id', 'id_admin');
    }
}
