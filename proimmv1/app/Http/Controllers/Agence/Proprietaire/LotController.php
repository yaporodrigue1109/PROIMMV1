<?php

namespace App\Http\Controllers\Agence\Proprietaire;

use App\Http\Controllers\Controller;
use App\Http\Requests\Agence\LotRequest;
use App\Repositories\Agence\Interfaces\LotRepositoryInterface;
use Illuminate\Http\JsonResponse;

class LotController extends Controller
{
    protected  $lotRepository;
    public function __construct(
         LotRepositoryInterface $lotRepository
    ) {
        $this->lotRepository = $lotRepository;
    }

    public function store(LotRequest $request, string $proprietaireId): JsonResponse
    {
        $info = getInfoAgent();
        $data = array_merge($request->validated(), [
            'proprietaire_id' => $proprietaireId,
            'agence_id'       => $this->agenceId(),
        ]);

        $lot = $this->lotRepository->create($data);

        return response()->json([
            'success' => true,
            'message' => 'Lot crÃ©Ã© avec succÃ¨s.',
            'lot'     => $lot->load(['region', 'ville']),
        ]);
    }

    public function update(LotRequest $request, string $id): JsonResponse
    {
        $lot = $this->lotRepository->update($id, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Lot mis Ã  jour avec succÃ¨s.',
            'lot'     => $lot,
        ]);
    }

    public function destroy(string $id): JsonResponse
    {
        $lot = $this->lotRepository->findById($id);

        if ($lot->proprietes()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Impossible de supprimer ce lot : il est deja lie a une propriete.',
            ], 422);
        }

        $this->lotRepository->delete($id);

        return response()->json([
            'success' => true,
            'message' => 'Lot supprime avec succes.',
        ]);
    }

    public function getByProprietaire(string $proprietaireId): JsonResponse
    {
        $info = getInfoAgent();
        $lots = $this->lotRepository->getAllByProprietaire(
            $proprietaireId,
            $info->users->agence_id
        );

        return response()->json(['lots' => $lots]);
    }
    private function agenceId(): string
    {
        return getInfoAgent()->users->agence_id;
    }

    private function userId(): string
    {
        return getInfoAgent()->users->id ?? 'system';
    }
}
