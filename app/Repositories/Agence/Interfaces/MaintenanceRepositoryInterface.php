<?php

namespace App\Repositories\Agence\Interfaces;

use App\Models\Maintenance;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface MaintenanceRepositoryInterface
{
    public function all(array $filters = []);
    public function find($id);
    public function create(array $data);
    public function update($id, array $data);
    public function delete($id);
    public function restore($id);
    public function forceDelete($id);
    public function getByStatut($statut);
    public function getByMaintenancier($maintenancierId);
    public function getByProprietaire($proprietaireId);
    public function getByAgence($agenceId);
    public function getByDateRange($startDate, $endDate);
    public function countByStatut();
    public function getMontantTotalParMois($year);

    /**
     * Retourne une liste paginée avec filtres optionnels.
     *
     * @param array $filters  ['statut', 'proprietaire_id', 'lot_id', 'prise_en_charge_par', ...]
     * @param int   $perPage
     */
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * Trouve une maintenance par son ID (avec ses détails).
     */
    public function findById(string $id, bool $withDetails = true): ?Maintenance;

    /**
     * Crée une maintenance ET ses détails en une transaction.
     *
     * @param array $data    Champs de la maintenance
     * @param array $details Tableau de détails (chaque entrée = un MaintenanceDetail)
     */
    public function createWithDetails(array $data, array $details): Maintenance;

    /**
     * Met à jour une maintenance et synchronise ses détails.
     *
     * @param string $id
     * @param array  $data
     * @param array  $details Si fourni, remplace tous les détails existants
     */
    public function updateWithDetails(string $id, array $data, array $details = []): Maintenance;

    /**
     * Change le statut d'une maintenance.
     */
    public function updateStatut(string $id, string $statut): Maintenance;

    /**
     * Retourne les maintenances d'un propriétaire.
     */
    public function findByProprietaire(string $proprietaireId): Collection;

    /**
     * Retourne les maintenances d'un lot.
     */
    public function findByLot(string $lotId): Collection;

    /**
     * Retourne les maintenances d'un bâtiment.
     */
    public function findByBatiment(string $batimentId): Collection;

    /**
     * Retourne les maintenances d'une porte.
     */
    public function findByPorte(string $porteId): Collection;
}