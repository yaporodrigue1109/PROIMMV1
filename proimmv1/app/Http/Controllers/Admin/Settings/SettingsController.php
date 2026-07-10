<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\SettingsRequest;
use App\Services\SettingService;  // ← Import correct
use App\Services\ConfigurationTarifService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SettingsController extends Controller
{
    protected SettingService $settingService;  // ← Type correct
    protected ConfigurationTarifService $tarifService;

    public function __construct(SettingService $settingService,ConfigurationTarifService $tarifService)  // ← Injection correcte
    {
        $this->settingService = $settingService;
        $this->tarifService = $tarifService;

        // Décommenter les middlewares si vous en avez besoin
        // $this->middleware('permission:view_settings')->only(['index']);
        // $this->middleware('permission:edit_settings')->only(['update']);
    }

    /**
     * Afficher le formulaire de configuration
     */
    public function index(): Response
    {
        $setting = $this->settingService->getSetting();
        $tarifs = $this->tarifService->getTarifsPourFormulaire();
//dd($tarifs);

        return Inertia::render('Admin/Settings/Index', [
            'setting' => $setting,
            'tarifs' => $tarifs,
        ]);
    }

    /**
     * Mettre à jour la configuration
     */
    public function update(SettingsRequest $request)
    {
        try {
            $setting = $this->settingService->updateSetting($request->validated());

            return redirect()
                ->route('admin.settings.index')
                ->with('success', 'Configuration mise à jour avec succès.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Erreur lors de la mise à jour : ' . $e->getMessage())
                ->withInput();
        }
    }

    public function updateTarification(Request $request)
    {
        try {
            // Valider les données
            $validation = $this->tarifService->validerConfig($request->all());

            if (!$validation['valide']) {
                return redirect()
                    ->back()
                    ->withErrors($validation['erreurs'])
                    ->withInput();
            }

            // Enregistrer les tarifs
            $tarif = $this->tarifService->saveTarifs($request->all());

            return redirect()
                ->route('admin.settings.index')
                ->with('success', 'Configuration des tarifs mise à jour avec succès.');

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Erreur lors de la mise à jour : ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * API: Calculer le prix pour une configuration donnée
     */
    public function calculerPrix(Request $request)
    {
        try {
            $nombreMois = $request->input('nombre_mois', 1);
            $modulesIds = $request->input('modules', []);

            $prix = $this->tarifService->calculerPrixAgence($nombreMois, $modulesIds);

            return response()->json([
                'success' => true,
                'data' => $prix,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * API: Récupérer les tarifs publics
     */
    public function getTarifsPublics()
    {
        try {
            $tarifs = $this->tarifService->getTarifsPublics();

            return response()->json([
                'success' => true,
                'data' => $tarifs,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
