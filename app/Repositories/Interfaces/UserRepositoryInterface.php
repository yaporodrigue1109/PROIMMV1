<?php

namespace App\Repositories\Interfaces;

use App\Models\User;
use Illuminate\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection;

interface UserRepositoryInterface
{
    public function getAll(array $filters = []): Collection;
    public function paginate(int $perPage = 15, array $filters = []): Paginator;
    public function getById(string $id): ?User;           // UUID string
    public function getByEmail(string $email): ?User;
    public function create(array $data): User;
    public function update(string $id, array $data): User;  // UUID string
    public function delete(string $id): bool;                // UUID string
    public function restore(string $id): bool;               // UUID string
    public function forceDelete(string $id): bool;           // UUID string
    public function getByAgence(string $agenceId, array $filters = []): Collection;
    public function getByRole(int $roleId, array $filters = []): Collection;
    public function getResponsables(): Collection;
    public function search(string $term, array $filters = []): Collection;
    public function emailExists(string $email, ?string $excludeId = null): bool;  // UUID string
    public function activate(string $id): User;              // UUID string
    public function deactivate(string $id): User;           // UUID string
    public function suspend(string $id): User;                // UUID string
    public function count(array $filters = []): int;
    public function getStatistics(): array;
}