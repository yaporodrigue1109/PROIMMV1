<?php

use App\Providers\AppServiceProvider;
use App\Providers\RepositoryServiceProvider;
use App\Services\Agence\TransactionAgenceServiceProvider;
use App\Providers\VenteBienServiceProvider;

return [
    AppServiceProvider::class,
    RepositoryServiceProvider::class,
    TransactionAgenceServiceProvider::class,
     VenteBienServiceProvider::class,
];
