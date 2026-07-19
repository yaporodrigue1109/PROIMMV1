<?php

namespace Tests\Unit;

use App\Http\Controllers\Agence\Caisse\CaisseController;
use App\Repositories\Agence\Interfaces\TransactionAgenceRepositoryInterface;
use App\Repositories\Agence\Repository\TransactionAgenceRepository;
use Tests\TestCase;

class TransactionAgenceBindingTest extends TestCase
{
    public function test_transaction_agence_repository_is_bound_for_controller_resolution(): void
    {
        $controller = $this->app->make(CaisseController::class);
        $repository = $this->app->make(TransactionAgenceRepositoryInterface::class);

        $this->assertInstanceOf(CaisseController::class, $controller);
        $this->assertInstanceOf(TransactionAgenceRepository::class, $repository);
    }
}
