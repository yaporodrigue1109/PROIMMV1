<?php

namespace App\Http\Controllers\Agence\Proprietaire;

use App\Http\Controllers\Controller;
use App\Http\Requests\Agence\LotRequest;
use App\Repositories\Agence\Interfaces\LotRepositoryInterface;
use App\Models\ProprietaireLot;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

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
        $data['sale_price'] = ($data['is_for_sale'] ?? false) ? $data['sale_price'] : null;
        $this->ensureLotIsUnique($data);

        $lot = $this->lotRepository->create($data);

        return response()->json([
            'success' => true,
            'message' => 'Lot crÃ©Ã© avec succÃ¨s.',
            'lot'     => $lot->load(['region', 'ville']),
        ]);
    }

    public function update(LotRequest $request, string $id): JsonResponse
    {
        $existingLot = $this->lotRepository->findById($id);
        $data = $request->validated();
        $data['sale_price'] = ($data['is_for_sale'] ?? false) ? $data['sale_price'] : null;
        $this->ensureLotIsUnique([
            ...$data,
            'proprietaire_id' => $existingLot->proprietaire_id,
            'agence_id' => $existingLot->agence_id,
        ], $existingLot->propreietaire_lot_id);

        if (($data['is_for_sale'] ?? false) && $existingLot->proprietes()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Un lot qui contient déjà une propriété ne peut pas être mis en vente comme lot entier.',
            ], 422);
        }

        $lot = $this->lotRepository->update($id, $data);

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

    private function ensureLotIsUnique(array $data, ?string $exceptLotId = null): void
    {
        $ownerId = (string) ($data['proprietaire_id'] ?? '');
        $agencyId = (string) ($data['agence_id'] ?? $this->agenceId());
        $lotNumber = trim((string) ($data['num_lot'] ?? ''));
        $blockNumber = trim((string) ($data['num_ilot'] ?? ''));
        $address = trim((string) ($data['adresse'] ?? ''));

        $baseQuery = fn () => ProprietaireLot::query()
            ->where('proprietaire_id', $ownerId)
            ->where('agence_id', $agencyId)
            ->when($exceptLotId, fn ($query) => $query->where('propreietaire_lot_id', '!=', $exceptLotId));

        if ($lotNumber !== '' && $blockNumber !== '' && $address !== '') {
            $sameLot = $baseQuery()
                ->whereRaw('LOWER(TRIM(num_lot)) = ?', [mb_strtolower($lotNumber)])
                ->whereRaw('LOWER(TRIM(num_ilot)) = ?', [mb_strtolower($blockNumber)])
                ->whereRaw('LOWER(TRIM(adresse)) = ?', [mb_strtolower($address)])
                ->exists();

            if ($sameLot) {
                throw ValidationException::withMessages([
                    'num_lot' => 'Ce propriétaire possède déjà ce même lot à cette adresse.',
                    'num_ilot' => 'Cette combinaison propriétaire, îlot, lot et adresse existe déjà.',
                    'adresse' => 'Cette combinaison propriétaire, îlot, lot et adresse existe déjà.',
                ]);
            }
        }
    }
}
