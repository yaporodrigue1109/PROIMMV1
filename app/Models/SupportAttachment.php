<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model; use Illuminate\Support\Str;
class SupportAttachment extends Model { protected $primaryKey='support_attachment_id'; public $incrementing=false; protected $keyType='string'; protected $fillable=['support_ticket_id','support_message_id','nom_original','chemin','mime_type','taille']; protected static function booted(): void { static::creating(fn(self $m) => $m->support_attachment_id ??= (string) Str::uuid()); } }
