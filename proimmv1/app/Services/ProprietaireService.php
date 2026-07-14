<?php

namespace App\Services;

use App\Repositories\Interfaces\ProprietaireRepositoryInterface;
use App\Models\Proprietaire;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProprietaireService
{
    protected  $proprietaireRepository;
    public function __construct(
         ProprietaireRepositoryInterface $proprietaireRepository
    ) {
        $this->proprietaireRepository = $proprietaireRepository;
    }

    public function getPaginated(string $agenceId, int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        return $this->proprietaireRepository->getAllByAgence($agenceId, $perPage, $filters);
    }

    public function getById(string $id, string $agenceId): ?Proprietaire
    {
        return $this->proprietaireRepository->findByIdAndAgence($id, $agenceId);
    }

    public function store(array $data, string $agenceId): Proprietaire
    {
        $proprietaireData = $this->extractProprietaireData($data);

        $agenceData       = $this->extractAgenceData($data);

      //  $proprietaireData['created_by'] = Auth::id();
        $proprietaireData['code']       = $this->generateCode();

        if (isset($data['photo']) && $data['photo'] instanceof \Illuminate\Http\UploadedFile) {
            $proprietaireData['photo'] = upload('proprietaire', 'png', 'photo', $data);
        }

        if (isset($data['photo_representant']) && $data['photo_representant'] instanceof \Illuminate\Http\UploadedFile) {
            $agenceData['photo_representant'] = upload('representant', 'png', 'photo_representant', $data);
        }

        return $this->proprietaireRepository->create($proprietaireData, $agenceData, $agenceId);
    }

    public function update(string $id, array $data): Proprietaire
    {
        $proprietaireData = $this->extractProprietaireData($data);
        $agenceData       = $this->extractAgenceData($data);

        if (isset($data['photo']) && $data['photo'] instanceof \Illuminate\Http\UploadedFile) {
            $existing = $this->proprietaireRepository->findById($id);
            $updatedPhoto = update('proprietaire', $existing?->photo, 'png', $data, 'photo');

            if (is_string($updatedPhoto) && $updatedPhoto !== '') {
                $proprietaireData['photo'] = $updatedPhoto;
            }
        }

        if (isset($data['photo_representant']) && $data['photo_representant'] instanceof \Illuminate\Http\UploadedFile) {
            $existing = $this->proprietaireRepository->findById($id);
            $liaison = $existing?->agences?->firstWhere('agence_id', getInfoAgent()->users->agence_id);
            $updatedPhoto = update('representant', $liaison?->photo_representant, 'png', $data, 'photo_representant');

            if (is_string($updatedPhoto) && $updatedPhoto !== '') {
                $agenceData['photo_representant'] = $updatedPhoto;
            }
        }

        return $this->proprietaireRepository->update($id, $proprietaireData, $agenceData);
    }

    public function destroy(string $id): bool
    {
        return $this->proprietaireRepository->delete($id);
    }

    public function activate(string $proprietaireAgenceId): bool
    {
        return $this->proprietaireRepository->activate($proprietaireAgenceId, Auth::id());
    }

    public function deactivate(string $proprietaireAgenceId): bool
    {
        return $this->proprietaireRepository->deactivate($proprietaireAgenceId, Auth::id());
    }

    // -------------------------------------------------------------------------
    // Helpers privés
    // -------------------------------------------------------------------------

    private function extractProprietaireData(array $data): array
    {
        return array_filter(
            array_intersect_key($data, array_flip([
                'name', 'tel1', 'tel2', 'type_pieces_id', 'numpiece','genre_id',
                'email', 'profession', 'nationalite', 'date_naiss',
                'lieu_naiss', 'region_id', 'ville_id', 'adresse', 'photo',"date_expiration_piece"
            ])),
            fn ($v) => $v !== null
        );
    }

    private function extractAgenceData(array $data): array
    {
        return array_filter(
            array_intersect_key($data, array_flip([
                'name_representant', 'adresse_representant',
                'tel1_representant', 'tel2_representant', 'email_representant',
                'genre_representant_id',
                'type_pieces_representant_id', 'numpiece_representant', 'photo_representant',
            ])),
            fn ($v) => $v !== null
        );
    }

    private function generateCode(): string
    {
        do {
            $letters = strtoupper(chr(rand(65, 90)) . chr(rand(65, 90)));
            $numbers = str_pad(rand(0, 99999), 5, '0', STR_PAD_LEFT);
            $code    = $letters . '-' . $numbers;
        } while (Proprietaire::where('code', $code)->exists());

        return $code;
    }

    private function uploadPhoto(\Illuminate\Http\UploadedFile $file): string
    {
        return $file->store('proprietaires/photos', 'public');
    }
}
