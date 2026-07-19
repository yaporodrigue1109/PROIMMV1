<?php

namespace App\Services\Agence;

use Illuminate\Support\ServiceProvider;
use App\Repositories\Agence\Interfaces\TransactionAgenceRepositoryInterface;
use App\Repositories\Agence\Repository\TransactionAgenceRepository;

class TransactionAgenceServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            TransactionAgenceRepositoryInterface::class,
            TransactionAgenceRepository::class
        );
    }

    public function boot(): void
    {
        //
    }
}