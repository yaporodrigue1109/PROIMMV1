<?php

namespace App\Console\Commands;

use App\Services\Agence\CaisseClotureService;
use Illuminate\Console\Command;

class CloturerCaissesCommand extends Command
{
    protected $signature = 'caisse:cloturer-expirees';
    protected $description = 'Clôture automatiquement les caisses restées ouvertes après minuit';

    public function handle(CaisseClotureService $service): int
    {
        $count = $service->cloturerCaissesExpirees();
        $this->info("{$count} caisse(s) clôturée(s) automatiquement.");

        return self::SUCCESS;
    }
}
