<?php

namespace App\Events;

use App\Models\Agence;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AgenceUpdated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Agence $agence,
        public ?string $oldStatut = null,
        public ?string $oldAbonnementId = null,
    ) {}
}