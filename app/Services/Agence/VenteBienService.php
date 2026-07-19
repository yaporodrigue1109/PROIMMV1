<?php

namespace App\Services\Agence;

use App\Repositories\Agence\Interfaces\VenteBienRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\VenteBien;

class VenteBienService
{
    protected VenteBienRepositoryInterface $venteBienRepository;

    public function __construct(VenteBienRepositoryInterface $venteBienRepository)
    {
        $this->venteBienRepository = $venteBienRepository;
    }

    /**
     * Créer une nouvelle vente
     */
    public function createVente(array $data): object
    {
        try {
            DB::beginTransaction();

            // Générer la référence
            if (empty($data['reference'])) {
                $data['reference'] = $this->venteBienRepository->generateReference($data['agence_id']);
            }

            // Calculer la commission si non définie
            if (empty($data['commission'])) {
                $data['commission'] = $data['prix_vente'] * 0.10;
            }

            // Calculer le montant propriétaire
            if (empty($data['montant_proprietaire'])) {
                $data['montant_proprietaire'] = $data['prix_vente'] - $data['commission'];
            }

            $vente = $this->venteBienRepository->create($data);

            DB::commit();

            return $vente;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la création de la vente: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Mettre à jour une vente
     */
    public function updateVente(string $id, array $data): bool
    {
        try {
            DB::beginTransaction();

            // Recalculer commission et montant propriétaire si le prix change
            if (isset($data['prix_vente'])) {
                $vente = $this->venteBienRepository->findById($id);
                if ($vente) {
                    if (!isset($data['commission'])) {
                        $data['commission'] = $data['prix_vente'] * 0.10;
                    }
                    if (!isset($data['montant_proprietaire'])) {
                        $data['montant_proprietaire'] = $data['prix_vente'] - $data['commission'];
                    }
                }
            }

            $result = $this->venteBienRepository->update($id, $data);

            DB::commit();

            return $result;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la mise à jour de la vente: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Marquer une vente comme terminée
     */
    public function terminerVente(string $id): bool
    {
        return $this->venteBienRepository->update($id, [
            'statut' => VenteBien::STATUT_TERMINE
        ]);
    }

    /**
     * Marquer une vente comme annulée
     */
    public function annulerVente(string $id): bool
    {
        return $this->venteBienRepository->update($id, [
            'statut' => VenteBien::STATUT_ANNULE
        ]);
    }

    /**
     * Récupérer les statistiques des ventes
     */
    public function getStatistics(string $agenceId): array
    {
        return $this->venteBienRepository->getStatisticsByAgence($agenceId);
    }

    /**
     * Récupérer les ventes avec filtres
     */
    public function getVentes(array $filters = []): Collection
    {
        return $this->venteBienRepository->getFiltered($filters);
    }

    /**
     * Récupérer les ventes d'une agence
     */
    public function getVentesByAgence(string $agenceId): Collection
    {
        return $this->venteBienRepository->getByAgence($agenceId);
    }

    /**
     * Récupérer une vente par son ID
     */
    public function getVenteById(string $id): ?object
    {
        return $this->venteBienRepository->findById($id);
    }

    /**
     * Vérifier si une vente est valide
     */
    public function validateVente(array $data): array
    {
        $errors = [];

        if (empty($data['propriete_id'])) {
            $errors[] = 'La propriété est requise.';
        }

        if (empty($data['agence_id'])) {
            $errors[] = "L'agence est requise.";
        }

        if (empty($data['prix_vente']) || $data['prix_vente'] <= 0) {
            $errors[] = 'Le prix de vente doit être supérieur à 0.';
        }

        if (empty($data['date_accord'])) {
            $errors[] = 'La date d\'accord est requise.';
        }

        if (empty($data['type_paiement'])) {
            $errors[] = 'Le type de paiement est requis.';
        }

        if (!in_array($data['type_paiement'] ?? '', VenteBien::TYPES_PAIEMENT)) {
            $errors[] = 'Le type de paiement est invalide.';
        }

        return $errors;
    }
}