<?php

namespace App\Repositories\Interfaces;

use App\Models\ConfigurationTarif;
use App\Models\ConfigurationTarifDuree;
use App\Models\ConfigurationTarifModule;

interface ConfigurationTarifRepositoryInterface
{
    /**
     * Récupérer le tarif principal (unique)
     */
    public function getTarif(): ConfigurationTarif;

    /**
     * Mettre à jour le tarif principal
     */
    public function updateTarif(array $data): ConfigurationTarif;

    /**
     * Récupérer toutes les durées disponibles
     */
    public function getDurees(): array;

    /**
     * Synchroniser les durées disponibles
     */
    public function syncDurees(array $durees): void;

    /**
     * Récupérer tous les modules
     */
    public function getModules(): array;

    /**
     * Récupérer les modules actifs
     */
    public function getModulesActifs(): array;

    /**
     * Mettre à jour les modules
     */
    public function updateModules(array $modules): void;

    /**
     * Ajouter un module
     */
    public function addModule(string $label, float $prixMensuel): ConfigurationTarifModule;

    /**
     * Supprimer un module
     */
    public function deleteModule(int $moduleId): bool;

    /**
     * Récupérer la configuration complète pour l'affichage
     */
    public function getCompleteConfig(): array;

    /**
     * Mettre à jour la configuration complète
     */
    public function updateCompleteConfig(array $data): ConfigurationTarif;
}