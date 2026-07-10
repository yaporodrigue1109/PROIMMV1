<?php

namespace App\Services;

use App\Repositories\Interfaces\ConfigurationTarifRepositoryInterface;
use App\Models\ConfigurationTarif;
use App\Models\ConfigurationTarifDuree;
use App\Models\ConfigurationTarifModule;

class ConfigurationTarifService
{
    protected ConfigurationTarifRepositoryInterface $repository;

    public function __construct(ConfigurationTarifRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Récupérer les tarifs pour l'affichage dans le formulaire
     */
    public function getTarifsPourFormulaire(): array
    {
            return $this->repository->getCompleteConfig();
    }

    /**
     * Enregistrer les tarifs depuis le formulaire
     */
    public function saveTarifs(array $data): ConfigurationTarif
    {
        return $this->repository->updateCompleteConfig($data);
    }

    /**
     * Calculer le prix total pour une agence
     * @param int $nombreMois La durée en mois
     * @param array $modulesIds IDs des modules optionnels choisis
     */
    public function calculerPrixAgence(int $nombreMois, array $modulesIds = []): array
    {
        $tarif = $this->repository->getTarif();
        $durees = $this->repository->getDurees();

        // Trouver le prix pour la durée demandée
        $duree = collect($durees)->firstWhere('nombre_mois', $nombreMois);
        $prixBase = $duree['prix_total'] ?? ($tarif->plan_prix_mensuel * $nombreMois);

        // Ajouter les modules optionnels
        $prixModules = 0;
        $modules = [];

        if (!empty($modulesIds)) {
            $modulesSelectionnes = collect($this->repository->getModulesActifs())
                ->whereIn('id', $modulesIds);

            foreach ($modulesSelectionnes as $module) {
                $prixModule = (float) ($module['prix_mensuel'] ?? 0) * $nombreMois;
                $prixModules += $prixModule;
                $modules[] = [
                    'id' => $module['id'],
                    'label' => $module['label'],
                    'prix_mensuel' => (float) $module['prix_mensuel'],
                    'prix_total' => $prixModule,
                ];
            }
        }

        $prixTotal = $prixBase + $prixModules;

        return [
            'plan' => [
                'nom' => $tarif->plan_nom,
                'prix_mensuel' => (float)$tarif->plan_prix_mensuel,
                'prix_base' => $prixBase,
            ],
            'modules' => $modules,
            'prix_modules' => $prixModules,
            'prix_total' => $prixTotal,
            'nombre_mois' => $nombreMois,
            'cycle' => $tarif->cycle_facturation,
            'delai_grace' => $tarif->delai_grace,
        ];
    }

    /**
     * Récupérer les tarifs publics (modules actifs uniquement)
     */
    public function getTarifsPublics(): array
    {
        $tarif = $this->repository->getTarif();

        return [
            'plan' => [
                'nom' => $tarif->plan_nom,
                'description' => $tarif->plan_description,
                'prix_mensuel' => (float)$tarif->plan_prix_mensuel,
                'cycle' => $tarif->cycle_facturation,
            ],
            'durees' => $this->repository->getDurees(),
            'modules' => collect($this->repository->getModulesActifs())
                ->map(fn($m) => [
                    'id' => $m['id'],
                    'label' => $m['label'],
                    'prix_mensuel' => (float) $m['prix_mensuel'],
                ])
                ->toArray(),
        ];
    }

    /**
     * Valider une configuration de tarifs
     */
    public function validerConfig(array $data): array
    {
        $erreurs = [];

        // Valider le nom du plan
        if (empty($data['plan_nom'])) {
            $erreurs[] = 'Le nom du plan est obligatoire';
        }

        // Valider les prix
        if (!isset($data['plan_prix_mensuel']) || $data['plan_prix_mensuel'] < 0) {
            $erreurs[] = 'Le prix mensuel doit être positif';
        }

        // Valider le délai de grâce
        if (!isset($data['delai_grace']) || $data['delai_grace'] < 0) {
            $erreurs[] = 'Le délai de grâce doit être positif';
        }

        // Valider au moins une durée disponible
        if (empty($data['durees'])) {
            $erreurs[] = 'Au moins une durée d\'abonnement doit être disponible';
        }

        // Valider les modules
        if (!empty($data['modules'])) {
            foreach ($data['modules'] as $key => $module) {
                if (!isset($module['prix_mensuel']) || $module['prix_mensuel'] < 0) {
                    $erreurs[] = "Le prix du module '{$module['label']}' doit être positif";
                }
            }
        }

        return [
            'valide' => empty($erreurs),
            'erreurs' => $erreurs,
        ];
    }
}
