<?php

namespace App\Console\Commands;

use App\Services\Agence\FacturationNotificationService;
use Illuminate\Console\Command;

class EnvoyerAlertesFacturation extends Command
{
    protected $signature = 'facturation:envoyer-alertes';
    protected $description = 'Envoie les rappels d’échéance et alertes de retard configurés par les agences';

    public function handle(FacturationNotificationService $notifications): int
    {
        $counts = $notifications->sendScheduledAlerts();
        $this->info("Rappels : {$counts['rappels']} | Retards : {$counts['retards']} | Erreurs : {$counts['erreurs']}");
        return self::SUCCESS;
    }
}
