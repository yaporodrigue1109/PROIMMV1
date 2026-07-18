<?php

namespace App\Http\Controllers\Agence\Maintenance;


use App\Http\Controllers\Controller;

use App\Services\Agence\FonctionMaintenanceService;
use Illuminate\Http\Request;

class FonctionMaintenanceController extends Controller
{
    protected $service;

    public function __construct(FonctionMaintenanceService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $filters = $request->only(['agence_id', 'per_page']);
        $fonctions = $this->service->getAllFonctions($filters);

        return response()->json([
            'success' => true,
            'data' => $fonctions
        ]);
    }

    public function show($id)
    {
        $fonction = $this->service->getFonction($id);

        if (!$fonction) {
            return response()->json([
                'success' => false,
                'message' => 'Fonction non trouvée'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $fonction
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            // 'agence_id' => 'required|exists:agences,agence_id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string'
        ]);


        try {
            $validated['agence_id'] = $this->agenceId();
            $fonction = $this->service->createFonction($validated);

            return back()->with('success' , 'Fonction créée avec succès');

//            return response()->json([
//                'success' => true,
//                'message' => 'Fonction créée avec succès',
//                'data' => $fonction
//            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string'
        ]);

        try {
            $fonction = $this->service->updateFonction($id, $validated);

            if (!$fonction) {
                return response()->json([
                    'success' => false,
                    'message' => 'Fonction non trouvée'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Fonction mise à jour avec succès',
                'data' => $fonction
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $result = $this->service->deleteFonction($id);

            if (!$result) {
                return response()->json([
                    'success' => false,
                    'message' => 'Fonction non trouvée'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Fonction supprimée avec succès'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
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
