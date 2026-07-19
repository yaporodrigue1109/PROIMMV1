<?php

namespace App\Http\Controllers\Agence\Maintenance;

use App\Http\Controllers\Controller;
use App\Models\MaintenanceCategory;
use App\Services\Agence\TypeMaintenanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;


class TypeMaintenanceController extends Controller
{
    protected $service;

    public function __construct(TypeMaintenanceService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $filters = $request->only(['agence_id', 'per_page']);
        $types = $this->service->getAllTypes($filters);

        return response()->json([
            'success' => true,
            'data' => $types
        ]);
    }

    public function show($id)
    {
        $type = $this->service->getType($id);

        if (!$type) {
            return response()->json([
                'success' => false,
                'message' => 'Type de maintenance non trouvé'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $type
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            // 'agence_id' => 'required|exists:agences,agence_id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'categorie' => 'nullable|string|max:255',
            'maintenance_category_id' => 'nullable|required_without:categorie|exists:maintenance_categories,maintenance_category_id',
            'duree_estimee' => 'nullable|numeric|min:0',
        ]);

        try {
            $validated['agence_id'] = $this->agenceId();
            $validated = $this->applyCategoryPayload($validated);
            $type = $this->service->createType($validated);

            return back()->with('success' , 'Type de maintenance créée avec succès');
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
            'categorie' => 'nullable|string|max:255',
            'maintenance_category_id' => 'nullable|required_without:categorie|exists:maintenance_categories,maintenance_category_id',
            'description' => 'nullable|string',
            'duree_estimee' => 'nullable|numeric|min:0',
        ]);

        try {
            $validated = $this->applyCategoryPayload($validated);
            $type = $this->service->updateType($id, $validated);

            if (!$type) {
                return response()->json([
                    'success' => false,
                    'message' => 'Type de maintenance non trouvé'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Type de maintenance mis à jour avec succès',
                'data' => $type
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
            $result = $this->service->deleteType($id);

            if (!$result) {

                return back()->with('errors','Type de maintenance non trouvé');
            }

            return back()->with('success','Type de maintenance supprimé avec succès');

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

    private function applyCategoryPayload(array $validated): array
    {
        $categoryId = $validated['maintenance_category_id'] ?? null;
        $legacyCategory = trim((string) ($validated['categorie'] ?? ''));

        if ($categoryId) {
            $category = MaintenanceCategory::query()->find($categoryId);
            if ($category) {
                $validated['categorie'] = $category->name;
                $validated['maintenance_category_id'] = $category->maintenance_category_id;
                return $validated;
            }
        }

        if ($legacyCategory !== '') {
            $category = MaintenanceCategory::firstOrCreate(
                ['name' => $legacyCategory],
                [
                    'slug' => Str::slug($legacyCategory),
                    'description' => null,
                    'is_active' => true,
                ]
            );

            $validated['categorie'] = $category->name;
            $validated['maintenance_category_id'] = $category->maintenance_category_id;
            return $validated;
        }

        $validated['categorie'] = null;
        $validated['maintenance_category_id'] = null;
        return $validated;
    }
}
