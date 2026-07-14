<?php

namespace App\Services\Agence;

use App\Repositories\Agence\Interfaces\MaintenanceRepositoryInterface;

class MaintenanceService
{
    protected  $repository;
    public function __construct(
         MaintenanceRepositoryInterface $repository
    ) {
        $this->repository = $repository;
    }

    public function getAllMaintenances(array $filters = [])
    {
        return $this->repository->all($filters);
    }

    public function getMaintenance(string $id)
    {
        return $this->repository->findById($id, withDetails: true);
    }

    /**
     * Crée une maintenance avec ses détails (transaction).
     *
     * @param array $data    Champs de l'en-tête (titre, agence_id, proprietaire_id…)
     * @param array $details Tableau de détails issus du formulaire
     */
    public function createMaintenance(array $data, array $details = [])
    {

        return $this->repository->createWithDetails($data, $details);
    }

    /**
     * Met à jour une maintenance et resynchronise ses détails si fournis.
     *
     * @param string $id
     * @param array  $data
     * @param array  $details  Vide = on ne touche pas aux détails existants
     */
    public function updateMaintenance(string $id, array $data, array $details = [])
    {
        return $this->repository->updateWithDetails($id, $data, $details);
    }

    public function deleteMaintenance(string $id): bool
    {
        return $this->repository->delete($id);
    }

    public function changerStatut(string $id, string $statut)
    {
        return $this->repository->updateStatut($id, $statut);
    }

    public function getStatistiques(): array
    {
        return [
            'par_statut'       => $this->repository->countByStatut(),
            'montant_par_mois' => $this->repository->getMontantTotalParMois(now()->year),
        ];
    }
}