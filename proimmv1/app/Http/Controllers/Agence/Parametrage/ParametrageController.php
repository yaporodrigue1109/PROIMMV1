<?php

namespace App\Http\Controllers\Agence\Parametrage;

use App\Http\Controllers\Controller;
use App\Http\Requests\Agence\UpdateParametrageGeneralRequest;
use App\Http\Requests\Agence\UpdateParametrageFacturationRequest;
use App\Http\Requests\Agence\UpdateParametrageLogosRequest;
use App\Http\Requests\Agence\UpdateParametrageSignaturesRequest;
use App\Http\Requests\Agence\UpdateParametrageNotificationsRequest;
use App\Models\Agence;
use App\Models\Region;
use App\Models\Ville;
use App\Models\ModePaiement;
use Illuminate\Http\Request;
use App\Repositories\Agence\Interfaces\ParametrageAgenceRepositoryInterface;
use App\Repositories\Agence\Repository\ParametrageAgenceRepository;
use App\Repositories\Interfaces\AgenceRepositoryInterface;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class ParametrageController extends Controller
{
    protected  $parametrageRepository;
    protected  $agenceRepository;

    public function __construct(ParametrageAgenceRepositoryInterface $parametrageRepository,AgenceRepositoryInterface $agenceRepository)
    {
        $this->parametrageRepository = $parametrageRepository;
        $this->agenceRepository = $agenceRepository;
    }

    /**
     * Afficher la page de paramétrage
     */
    public function index()
    {
        $agenceId = $this->agenceId();
        $agence = $this->agenceRepository->findById($agenceId);
        if (method_exists($agence, 'loadMissing')) {
            $agence->loadMissing(['region', 'ville', 'parametrage']);
        }

        $regions = Region::all();
        $villes = Ville::where('region_id', $agence->region_id)->get() ?? [];
        $modePaiement = ModePaiement::all();

        $parametrage = $this->parametrageRepository->getByAgence($agenceId);

        return Inertia::render('Agence/Parametrage/Index', [
            'parametrage' => $parametrage,
            'agence' => $agence,
            'regions' => $regions,
            'villes' => $villes,
            'modePaiement' => $modePaiement,
        ]);
    }

    /**
     * Mettre à jour les paramètres généraux
     */
    public function updateAgence(Request $request)
    {
        try {
            $agenceId = $this->agenceId();
            $this->agenceRepository->update($agenceId, $request->all());
            //$this->parametrageRepository->updateGeneral($agenceId, $request->validated());

            return redirect()->back()->with('success', 'Paramètres généraux mis à jour avec succès.');
        } catch (\Exception $e) {
            dd($e->getMessage());
            return redirect()->back()->with('error', 'Une erreur est survenue : ' . $e->getMessage());
        }
    }

    /**
     * Mettre à jour les paramètres de facturation
     */
    public function updateFacturation(Request $request)
    {
        try {
           // dd($request->all());
            $agenceId = $this->agenceId();
            $this->parametrageRepository->updateFacturation($agenceId, $request->all());

            return redirect()->back()->with('success', 'Paramètres de facturation mis à jour avec succès.');
        } catch (\Exception $e) {
            dd($e->getMessage());
            return redirect()->back()->with('error', 'Une erreur est survenue : ' . $e->getMessage());
        }
    }

    /**
     * Mettre à jour les logos
     */
    public function updateLogos(UpdateParametrageLogosRequest $request)
    {
        try {
            $agenceId = $this->agenceId();
            $files = $request->file();
            $data = $request->validated();

            $this->parametrageRepository->updateLogos($agenceId, $files, $data);

            return redirect()->back()->with('success', 'Logos mis à jour avec succès.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Une erreur est survenue : ' . $e->getMessage());
        }
    }

    /**
     * Mettre à jour les signatures
     */
    public function updateSignatures(UpdateParametrageSignaturesRequest $request)
    {
        try {
            $agenceId = $this->agenceId();
            $files = $request->file();
            $data = $request->validated();

            $this->parametrageRepository->updateSignatures($agenceId, $files, $data);

            return redirect()->back()->with('success', 'Signatures mises à jour avec succès.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Une erreur est survenue : ' . $e->getMessage());
        }
    }

    /**
     * Mettre à jour les notifications
     */
    public function updateNotifications(UpdateParametrageNotificationsRequest $request)
    {
        try {
            $agenceId = $this->agenceId();
            $this->parametrageRepository->updateNotifications($agenceId, $request->validated());

            return redirect()->back()->with('success', 'Paramètres de notification mis à jour avec succès.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Une erreur est survenue : ' . $e->getMessage());
        }
    }

    public function updateGeneral(Request $request)
    {
        try {
            $agenceId = $this->agenceId();
            $this->parametrageRepository->updateGeneral($agenceId, $request->all());

            return redirect()->back()->with('success', 'Paramètres de notification mis à jour avec succès.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Une erreur est survenue : ' . $e->getMessage());
        }
    }

    public function agenceId(){

      return  getInfoAgent()->users->agence_id;
    }
}
