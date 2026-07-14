<?php

namespace App\Repositories\Agence\Interfaces;

interface MaintenancierRepositoryInterface
{
    public function all(array $filters = []);
    public function find($id);
    public function create(array $data);
    public function update($id, array $data);
    public function delete($id);
    public function getByFonction($fonctionId);
    public function getByAgence($agenceId);
    public function getActifs();
    public function getDisponibles();
}