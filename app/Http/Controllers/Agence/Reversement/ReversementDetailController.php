<?php

namespace App\Http\Controllers;

use App\Services\Agence\ReversementDetailService;
use App\Http\Requests\Agence\Reversement\ReversementDetailRequest;
use App\Http\Resources\ReversementDetailResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;


class ReversementDetailController extends Controller
{
    protected ReversementDetailService $service;

    public function __construct(ReversementDetailService $service)
    {
        $this->service = $service;
    }

    /**
     * Créer un détail de reversement
     */
    public function store(ReversementDetailRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            $detail = $this->service->createDetail($data);

            return response()->json([
                'success' => true,
                'message' => 'Détail créé avec succès',
                'data' => new ReversementDetailResource($detail)
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Récupérer un détail par son ID
     */
    public function show(string $id): JsonResponse
    {
        try {
            $detail = $this->service->getDetail($id);

            if (!$detail) {
                return response()->json([
                    'success' => false,
                    'message' => 'Détail non trouvé'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => new ReversementDetailResource($detail)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Liste des détails d'un reversement
     */
    public function index(string $reversementId): JsonResponse
    {
        try {
            $details = $this->service->getDetailsByReversement($reversementId);

            return response()->json([
                'success' => true,
                'data' => ReversementDetailResource::collection($details)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Mettre à jour un détail
     */
    public function update(Request $request, string $id): JsonResponse
    {
        try {
            $request->validate([
                'loyer_paye' => 'sometimes|integer|min:0',
                'arriere_paye' => 'sometimes|integer|min:0'
            ]);

            $data = $request->all();
            $detail = $this->service->updateDetail($id, $data);

            return response()->json([
                'success' => true,
                'message' => 'Détail mis à jour avec succès',
                'data' => new ReversementDetailResource($detail)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Supprimer un détail
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $result = $this->service->deleteDetail($id);

            if (!$result) {
                return response()->json([
                    'success' => false,
                    'message' => 'Détail non trouvé'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Détail supprimé avec succès'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Résumé des détails d'un reversement
     */
    public function summary(string $reversementId): JsonResponse
    {
        try {
            $summary = $this->service->getSummaryByReversement($reversementId);

            return response()->json([
                'success' => true,
                'data' => $summary
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }
}