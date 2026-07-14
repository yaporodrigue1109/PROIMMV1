<?php

namespace App\Repositories\Interfaces;


use App\Models\Configuration;

interface SettingRepositoryInterface
{
    /**
     * Récupérer la configuration
     */
    public function get(): Configuration;

    /**
     * Mettre à jour la configuration
     */
    public function update(array $data): Configuration;

    /**
     * Récupérer une valeur spécifique
     */
    public function getValue(string $key): mixed;

    /**
     * Définir une valeur spécifique
     */
    public function setValue(string $key, mixed $value): void;
}