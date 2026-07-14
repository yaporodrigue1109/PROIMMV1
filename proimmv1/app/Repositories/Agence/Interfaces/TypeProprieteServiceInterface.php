<?php

namespace App\Repositories\Agence\Interfaces;

use App\Models\TypePropriete;
use Illuminate\Database\Eloquent\Collection;

interface TypeProprieteServiceInterface
{
    /**
     * Lister tous les types de l'agence courante.
     */
    public function list(): Collection;

    /**
     * Créer un nouveau type pour l'agence courante.
     *
     * @throws \Exception si le nom est déjà utilisé
     */
    public function create(array $data): TypePropriete;

    /**
     * Mettre à jour un type.
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function update(int $id, array $data): TypePropriete;

    /**
     * Supprimer un type.
     *
     * @throws \Exception si le type est utilisé par des propriétés
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function delete(int $id): void;

    /**
     * Récupérer un type par ID (avec vérification agence).
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function findOrFail(int $id): TypePropriete;
}