<?php


namespace App\Repositories\Agence\Interfaces;

interface PersonnelRepositoryInterface
{
    public function getAllByAgence(string $agenceId, array $filters = []);
    public function findById(string $id);
    public function create(array $data): mixed;
    public function update(string $id, array $data): mixed;
    public function delete(string $id): bool;
    public function activate(string $id): bool;
    public function deactivate(string $id): bool;
}