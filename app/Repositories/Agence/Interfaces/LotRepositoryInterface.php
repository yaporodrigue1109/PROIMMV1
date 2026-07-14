<?php

namespace App\Repositories\Agence\Interfaces;

use App\Models\ProprietaireLot;

interface LotRepositoryInterface
{
    public function getAllByProprietaire(string $proprietaireId, string $agenceId);
    public function findById(string $id): ProprietaireLot;
    public function create(array $data): ProprietaireLot;
    public function update(string $id, array $data): ProprietaireLot;
    public function delete(string $id): bool;
}