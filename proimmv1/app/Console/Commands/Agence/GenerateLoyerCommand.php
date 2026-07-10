<?php

namespace App\Console\Commands\Agence;

use App\Jobs\GenerateLoyerMensuel;
use Carbon\Carbon;
use Illuminate\Console\Command;

/**
 * Commande pour déclencher manuellement la génération des loyers.
 *
 * Usage :
 *   php artisan loyer:generate                          → mois courant, toutes agences
 *   php artisan loyer:generate --mois=8 --annee=2026    → mois/année précis
 *   php artisan loyer:generate --agence=AGC_001         → agence spécifique
 */
class GenerateLoyerCommand extends Command
{
    protected $signature = 'loyer:generate
                            {--mois=   : Mois cible (1-12). Défaut : mois courant}
                            {--annee=  : Année cible. Défaut : année courante}
                            {--agence= : ID agence (optionnel)}';

    protected $description = 'Génère les factures de loyer pour le mois cible';

    public function handle(): int
    {

        $mois   = (int) ($this->option('mois')   ?: now()->month);
        $annee  = (int) ($this->option('annee')  ?: now()->year);
        $agence = $this->option('agence') ?: null;

        if ($mois < 1 || $mois > 12) {
            $this->error('Le mois doit être compris entre 1 et 12.');
            return self::FAILURE;
        }

        $date = Carbon::createFromDate($annee, $mois, 1)->startOfMonth();

        $this->info("Génération des loyers pour {$date->format('m/Y')}" . ($agence ? " (agence: {$agence})" : ' (toutes agences)'));

        GenerateLoyerMensuel::dispatch($date, $agence);

        $this->info('Job dispatché avec succès. Consultez les logs pour le résultat.');

        return self::SUCCESS;
    }
}