<?php

namespace App\Providers;

use App\Repositories\Interfaces\SettingRepositoryInterface;
use App\Repositories\Repository\SettingRepository;
use App\Services\SettingService;
use Illuminate\Support\ServiceProvider;

class SettingServiceProvider extends ServiceProvider
{
    /**
     * Enregistrer les services
     */
    public function register(): void
    {
        // Enregistrer le repository dans le conteneur

    }

    /**
     * Déclencher les services
     */
    public function boot(): void
    {
        //
    }
}