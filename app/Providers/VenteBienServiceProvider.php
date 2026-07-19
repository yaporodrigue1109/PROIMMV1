<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\Agence\Interfaces\VenteBienRepositoryInterface;
use App\Repositories\Agence\VenteBienRepository;
use App\Services\Agence\VenteBienService;

class VenteBienServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            VenteBienRepositoryInterface::class,
            VenteBienRepository::class
        );

        $this->app->singleton(VenteBienService::class, function ($app) {
            return new VenteBienService(
                $app->make(VenteBienRepositoryInterface::class)
            );
        });
    }

    public function boot(): void
    {
        //
    }
}