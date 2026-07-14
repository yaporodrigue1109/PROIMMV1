<?php

namespace App\Repositories\Agence\Interfaces;

use App\Models\MaintenanceDetail;
use Illuminate\Database\Eloquent\Collection;

interface MaintenanceDetailRepositoryInterface
{
    /**
     * Retourne tous les détails d'une maintenance.
     */
    public function findByMaintenance(string $maintenanceId): Collection;

    /**
     * Trouve un détail par son ID.
     */
    public function findById(string $id): ?MaintenanceDetail;

    /**
     * Crée un détail.
     */
    public function create(array $data): MaintenanceDetail;

    /**
     * Met à jour un détail.
     */
    public function update(string $id, array $data): MaintenanceDetail;

    /**
     * Supprime (soft delete) un détail.
     */
    public function delete(string $id): bool;

    /**
     * Supprime tous les détails d'une maintenance (pour re-synchronisation).
     */
    public function deleteByMaintenance(string $maintenanceId): int;

    /**
     * Insère plusieurs détails d'un coup pour une maintenance.
     *
     * @param int   $maintenanceId
     * @param array $details  Tableau associatif de données
     */
    public function bulkInsert(string $maintenanceId, array $details): bool;

    /**
     * Change le statut d'un détail.
     */
    public function updateStatut(string $id, string $statut): MaintenanceDetail;

    /**
     * Retourne les détails assignés à un maintenancier.
     */
    public function findByMaintenancier(string $maintenancierId): Collection;
}