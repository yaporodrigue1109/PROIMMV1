<?php

namespace App\Console\Commands;

use App\Models\Loyer;
use Illuminate\Console\Command;

class ActualiserStatutsLoyers extends Command
{
    protected $signature = 'loyer:actualiser-statuts';
    protected $description = 'Passe en retard les loyers non réglés dont la date limite est dépassée';

    public function handle(): int
    {
        $updated = Loyer::query()
            ->where('statut', Loyer::STATUT_EN_COURS)
            ->where('montant_restant', '>', 0)
            ->whereNotNull('date_limit_paiement')
            ->whereDate('date_limit_paiement', '<', today())
            ->update([
                'statut' => Loyer::STATUT_IMPAYE,
                'updated_by' => 'system',
                'updated_at' => now(),
            ]);

        $this->info("{$updated} loyer(s) passé(s) en paiement en retard.");

        return self::SUCCESS;
    }
}
