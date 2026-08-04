<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class SupportTicket extends Model
{
    protected $primaryKey = 'support_ticket_id'; public $incrementing = false; protected $keyType = 'string';
    protected $fillable = ['agence_id','demandeur_id','categorie','sujet','description','statut','priorite','resolved_at'];
    protected $casts = ['resolved_at' => 'datetime'];
    protected static function booted(): void { static::creating(function (self $ticket) { $ticket->support_ticket_id ??= (string) Str::uuid(); $ticket->reference ??= 'SUP-'.now()->format('ymd').'-'.strtoupper(Str::random(5)); }); }
    public function messages() { return $this->hasMany(SupportMessage::class, 'support_ticket_id')->oldest(); }
    public function attachments() { return $this->hasMany(SupportAttachment::class, 'support_ticket_id'); }
    public function demandeur() { return $this->belongsTo(User::class, 'demandeur_id', 'id_users'); }
    public function agence() { return $this->belongsTo(Agence::class, 'agence_id', 'agence_id'); }
}
