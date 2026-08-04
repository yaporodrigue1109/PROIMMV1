<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model; use Illuminate\Support\Str;
class SupportMessage extends Model { protected $primaryKey='support_message_id'; public $incrementing=false; protected $keyType='string'; protected $fillable=['support_ticket_id','auteur_id','auteur_type','contenu']; protected static function booted(): void { static::creating(fn(self $m) => $m->support_message_id ??= (string) Str::uuid()); } public function ticket(){ return $this->belongsTo(SupportTicket::class,'support_ticket_id'); } }
