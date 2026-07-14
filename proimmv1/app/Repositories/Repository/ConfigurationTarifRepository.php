<?php

namespace App\Repositories\Repository;

use App\Repositories\Interfaces\ConfigurationTarifRepositoryInterface;
use App\Models\ConfigurationTarif;
use App\Models\ConfigurationTarifDuree;
use App\Models\ConfigurationTarifModule;
use Illuminate\Support\Facades\Schema;

class ConfigurationTarifRepository implements ConfigurationTarifRepositoryInterface
{
    protected ConfigurationTarif $tarifModel;
    protected ConfigurationTarifDuree $dureeModel;
    protected ConfigurationTarifModule $moduleModel;

    public function __construct(ConfigurationTarif $tarifModel, ConfigurationTarifDuree $dureeModel, ConfigurationTarifModule $moduleModel)
    {
        $this->tarifModel = $tarifModel;
        $this->dureeModel = $dureeModel;
        $this->moduleModel = $moduleModel;
    }

    private function tablesExist(): bool
    {
        return Schema::hasTable('configuration_tarifs')
            && Schema::hasTable('configuration_tarif_durees')
            && Schema::hasTable('configuration_tarif_modules');
    }

    private function defaultConfig(): array
    {
        return [
            'id' => null,
            'plan_nom' => 'Abonnement de base',
            'plan_prix_mensuel' => 0,
            'delai_grace' => 0,
            'cycle_facturation' => 'mensuel',
            'plan_description' => null,
        ];
    }

    /**
     * Récupérer le tarif principal (créer s'il n'existe pas)
     */
    public function getTarif(): ConfigurationTarif
    {
        if (!$this->tablesExist()) {
            return $this->tarifModel->newInstance($this->defaultConfig());
        }

        $tarif = $this->tarifModel->first();

        if (!$tarif) {
            $tarif = $this->tarifModel->create([
                'plan_nom' => 'Abonnement de base',
                'plan_prix_mensuel' => 0,
                'delai_grace' => 0,
                'cycle_facturation' => 'mensuel',
            ]);
        }

        return $tarif;
    }

    /**
     * Mettre à jour le tarif principal
     */
    public function updateTarif(array $data): ConfigurationTarif
    {
        $tarif = $this->getTarif();
        $tarif->update($data);
        return $tarif->fresh();
    }

    /**
     * Récupérer toutes les durées disponibles
     */
    public function getDurees(): array
    {
        if (!$this->tablesExist()) {
            return [
                ['id' => 1, 'nombre_mois' => 1, 'label' => '1 mois', 'prix_reduit' => 0, 'prix_total' => 0],
                ['id' => 2, 'nombre_mois' => 3, 'label' => '3 mois', 'prix_reduit' => 0, 'prix_total' => 0],
                ['id' => 3, 'nombre_mois' => 6, 'label' => '6 mois', 'prix_reduit' => 0, 'prix_total' => 0],
                ['id' => 4, 'nombre_mois' => 12, 'label' => '12 mois (1 an)', 'prix_reduit' => 0, 'prix_total' => 0],
                ['id' => 5, 'nombre_mois' => 24, 'label' => '24 mois (2 ans)', 'prix_reduit' => 0, 'prix_total' => 0],
                ['id' => 6, 'nombre_mois' => 36, 'label' => '36 mois (3 ans)', 'prix_reduit' => 0, 'prix_total' => 0],
            ];
        }

        $tarif = $this->getTarif();
        return $tarif->durees()
            ->orderBy('nombre_mois')
            ->get()
            ->map(fn($duree) => [
                'id' => $duree->id,
                'nombre_mois' => $duree->nombre_mois,
                'label' => $duree->getLabel(),
                'prix_reduit' => $duree->prix_reduit,
                'prix_total' => $duree->getPrixTotal(),
            ])
            ->toArray();
    }

    /**
     * Synchroniser les durées disponibles
     */
    public function syncDurees(array $durees): void
    {
        $tarif = $this->getTarif();

        // Supprimer les durées existantes
        $tarif->durees()->delete();

        // Créer les nouvelles durées
        foreach ($durees as $nombreMois) {
            $tarif->durees()->create([
                'nombre_mois' => $nombreMois,
            ]);
        }
    }

    /**
     * Récupérer tous les modules
     */
    public function getModules(): array
    {
        if (!$this->tablesExist()) {
            return [];
        }

        $tarif = $this->getTarif();
        return $tarif->modules()
            ->ordered()
            ->get()
            ->map(fn($module) => [
                'id' => $module->id,
                'label' => $module->label,
                'prix_mensuel' => $module->prix_mensuel,
                'actif' => $module->actif,
                'ordre' => $module->ordre,
            ])
            ->toArray();
    }

    /**
     * Récupérer les modules actifs
     */
    public function getModulesActifs(): array
    {
        if (!$this->tablesExist()) {
            return [];
        }

        $tarif = $this->getTarif();
        return $tarif->modulesActifs()
            ->ordered()
            ->get()
            ->toArray();
    }

    /**
     * Mettre à jour les modules
     */
    public function updateModules(array $modules): void
    {
        $tarif = $this->getTarif();

        foreach ($modules as $key => $moduleData) {
            $module = $tarif->modules()->find($key);

            if ($module) {
                $module->update([
                    'label' => $moduleData['label'] ?? $module->label,
                    'prix_mensuel' => $moduleData['prix_mensuel'] ?? $module->prix_mensuel,
                    'actif' => isset($moduleData['actif']) ? (bool)$moduleData['actif'] : $module->actif,
                ]);
            }
        }
    }

    /**
     * Ajouter un module
     */
    public function addModule(string $label, float $prixMensuel): ConfigurationTarifModule
    {
        $tarif = $this->getTarif();

        return $tarif->modules()->create([
            'label' => $label,
            'prix_mensuel' => $prixMensuel,
            'actif' => true,
        ]);
    }

    /**
     * Supprimer un module
     */
    public function deleteModule(int $moduleId): bool
    {
        return $this->moduleModel->destroy($moduleId) > 0;
    }

    /**
     * Récupérer la configuration complète pour l'affichage
     */
    public function getCompleteConfig(): array
    {
        if (!$this->tablesExist()) {
            return [
                'id' => null,
                'plan_nom' => 'Abonnement de base',
                'plan_prix_mensuel' => 0.0,
                'delai_grace' => 0,
                'cycle_facturation' => 'mensuel',
                'plan_description' => null,
                'durees' => [1, 3, 6, 12, 24, 36],
                'modules' => [],
            ];
        }

        $tarif = $this->getTarif();

        return [
            'id' => $tarif->id,
            'plan_nom' => $tarif->plan_nom,
            'plan_prix_mensuel' => (float)$tarif->plan_prix_mensuel,
            'delai_grace' => $tarif->delai_grace,
            'cycle_facturation' => $tarif->cycle_facturation,
            'plan_description' => $tarif->plan_description,
            'durees' => $tarif->durees()
                ->pluck('nombre_mois')
                ->toArray(),
            'modules' => $tarif->modules()
                ->ordered()
                ->get()
                ->mapWithKeys(fn($module, $key) => [
                    $key => [
                        'id' => $module->id,
                        'label' => $module->label,
                        'prix_mensuel' => (float)$module->prix_mensuel,
                        'actif' => $module->actif,
                    ]
                ])
                ->toArray(),
        ];
    }

    /**
     * Mettre à jour la configuration complète
     */
    public function updateCompleteConfig(array $data): ConfigurationTarif
    {
        $tarif = $this->getTarif();

        // Mettre à jour le tarif principal
        $tarifData = [
            'plan_nom' => $data['plan_nom'] ?? $tarif->plan_nom,
            'plan_prix_mensuel' => $data['plan_prix_mensuel'] ?? $tarif->plan_prix_mensuel,
            'delai_grace' => $data['delai_grace'] ?? $tarif->delai_grace,
            'cycle_facturation' => $data['cycle_facturation'] ?? $tarif->cycle_facturation,
            'plan_description' => $data['plan_description'] ?? $tarif->plan_description,
        ];

        $tarif->update($tarifData);

        // Mettre à jour les durées si fournies
        if (isset($data['durees'])) {
            $this->syncDurees($data['durees']);
        }

        // Mettre à jour les modules si fournis
        if (isset($data['modules'])) {
            $this->updateModules($data['modules']);
        }

        return $tarif->fresh();
    }
}
