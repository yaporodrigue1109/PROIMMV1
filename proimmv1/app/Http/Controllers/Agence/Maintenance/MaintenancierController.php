<?php

namespace App\Http\Controllers\Agence\Maintenance;

use App\Services\Agence\MaintenancierService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use Inertia\Inertia;

class MaintenancierController extends Controller
{
    protected $service;

    public function __construct(MaintenancierService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $filters = $request->only(['fonction_id', 'agence_id', 'statut', 'per_page']);
        $maintenanciers = $this->service->getAllMaintenanciers($filters);

        return response()->json([
            'success' => true,
            'data' => $maintenanciers
        ]);
    }

    public function showMaintenancier($id)
    {
        // Comme dans index(), mais pour un seul maintenancier
        $maintenancier = $this->service->getMaintenancier($id);



        if (!$maintenancier) {
            abort(404, 'Maintenancier non trouvé');
        }

        return Inertia::render('Agence/Maintenance/ShowMaintenancier', [
            'maintenancier' => $maintenancier,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'fonction_maintenance_id' => 'required|exists:fonction_maintenance,fonction_maintenance_id',
            'entreprise' => 'nullable|string',
            'name' => 'required|string|max:255',
            'tel1' => 'required|string|max:20',
            'tel2' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'adresse' => 'nullable|string',
            'type_piece_id' => 'required|exists:type_pieces,type_pieces_id',
            'numero_piece' => 'required|string|max:50',
            'date_validite_piece' => 'nullable|date',
            'statut' => ['nullable', Rule::in(['0', '1'])]
        ]);

        try {
            $validated['agence_id'] = $this->agenceId();

            $maintenancier = $this->service->createMaintenancier($validated);
            return back()->with('success' , 'Maintenancier créé avec succès');
           // dd($validated);
//            return response()->json([
//                'success' => true,
//                'message' => 'Maintenancier créé avec succès',
//                'data' => $maintenancier
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
            'fonction_maintenance_id' => 'required|exists:fonction_maintenance,fonction_maintenance_id',
            'entreprise' => 'nullable|string',
            'name' => 'required|string|max:255',
            'tel1' => 'required|string|max:20',
            'tel2' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'adresse' => 'nullable|string',
            'type_piece_id' => 'required|exists:type_pieces,type_pieces_id',
            'numero_piece' => 'required|string|max:50',
            'date_validite_piece' => 'nullable|date',
            'statut' => ['nullable', Rule::in(['0', '1'])]
        ]);

        try {
            $maintenancier = $this->service->updateMaintenancier($id, $validated);

            if (!$maintenancier) {
                return response()->json([
                    'success' => false,
                    'message' => 'Maintenancier non trouvé'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Maintenancier mis à jour avec succès',
                'data' => $maintenancier
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
            $result = $this->service->deleteMaintenancier($id);

            if (!$result) {
                return response()->json([
                    'success' => false,
                    'message' => 'Maintenancier non trouvé'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Maintenancier supprimé avec succès'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function disponibles()
    {
        try {
            $maintenanciers = $this->service->getDisponibles();

            return response()->json([
                'success' => true,
                'data' => $maintenanciers
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
