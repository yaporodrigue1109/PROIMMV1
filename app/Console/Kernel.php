<?php

// Dans App\Console\Kernel — méthode schedule()
// Ajouter cette entrée pour déclencher la génération chaque 1er du mois à 00:05
namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        \App\Console\Commands\Agence\GenerateLoyerCommand::class,
    ];

protected function schedule(Schedule $schedule): void
{
    // Les tâches sont déclarées dans routes/console.php (structure Laravel actuelle).
}

    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}

// ---------------------------------------------------------------------------
// IMPORTANT : s'assurer que le cron Laravel tourne sur le serveur
// Ajouter dans crontab (crontab -e) :
//
//   * * * * * cd /var/www/html && php artisan schedule:run >> /dev/null 2>&1
// ---------------------------------------------------------------------------
