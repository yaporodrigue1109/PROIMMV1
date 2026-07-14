<?php

namespace App\Repositories\Agence\Interfaces;

use App\Models\ParametrageAgence;
use Illuminate\Http\UploadedFile;

interface ParametrageAgenceRepositoryInterface
{
    /**
     * Récupérer les paramètres d'une agence
     */
    public function getByAgence(string $agenceId): ParametrageAgence;

    /**
     * Mettre à jour les paramètres généraux
     */
    public function updateGeneral(string $agenceId, array $data): ParametrageAgence;

    /**
     * Mettre à jour les paramètres de facturation
     */
    public function updateFacturation(string $agenceId, array $data): ParametrageAgence;

    /**
     * Mettre à jour les logos
     */
    public function updateLogos(string $agenceId, array $files, array $data): ParametrageAgence;

    /**
     * Mettre à jour les signatures
     */
    public function updateSignatures(string $agenceId, array $files, array $data): ParametrageAgence;

    /**
     * Mettre à jour les notifications
     */
    public function updateNotifications(string $agenceId, array $data): ParametrageAgence;

    /**
     * Upload d'un fichier
     */
    public function uploadFile(UploadedFile $file, string $path): string;
}