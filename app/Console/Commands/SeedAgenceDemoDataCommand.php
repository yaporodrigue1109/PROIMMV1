<?php

namespace App\Console\Commands;

use App\Models\Agence;
use App\Services\AgenceDemoDataService;
use Illuminate\Console\Command;

class SeedAgenceDemoDataCommand extends Command
{
    protected $signature = 'agences:seed-demo-data {agence? : Identifiant d’une agence précise}';
    protected $description = 'Ajoute les données fictives aux agences sans premier abonnement';

    public function handle(AgenceDemoDataService $demoDataService): int
    {
        $query = Agence::query()->where('statut', 'en_demo')->whereNull('abonnement_id');
        if ($agenceId = $this->argument('agence')) {
            $query->where('agence_id', $agenceId);
        }

        $count = 0;
        $query->each(function (Agence $agence) use ($demoDataService, &$count) {
            $actorId = (string) ($agence->responsable_id ?: $agence->created_by ?: 'demo-system');
            $demoDataService->seed($agence, $actorId);
            $count++;
        });

        $this->info("Données de démonstration synchronisées pour {$count} agence(s), sans modifier le personnel.");

        return self::SUCCESS;
    }
}
