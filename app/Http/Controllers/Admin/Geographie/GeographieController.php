<?php

namespace App\Http\Controllers\Admin\Geographie;

use App\Http\Controllers\Controller;
use App\Models\Pays;
use App\Models\Region;
use App\Models\Ville;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class GeographieController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Geographie/Index', [
            'pays' => Pays::withCount('regions')->orderBy('name')->get(),
            'regions' => Region::with('pays:id,name')->withCount('villes')->orderBy('name')->get(),
            'villes' => Ville::with('region:id,name,pays_id')->orderBy('name')->get(),
        ]);
    }

    public function storePays(Request $request): RedirectResponse
    {
        Pays::create($this->paysData($request));
        return back()->with('success', 'Pays créé avec succès.');
    }

    public function updatePays(Request $request, Pays $pays): RedirectResponse
    {
        $pays->update($this->paysData($request, $pays));
        return back()->with('success', 'Pays mis à jour.');
    }

    public function destroyPays(Pays $pays): RedirectResponse
    {
        if ($pays->regions()->exists()) return back()->with('error', 'Ce pays contient des régions et ne peut pas être supprimé.');
        $pays->delete();
        return back()->with('success', 'Pays supprimé.');
    }

    public function storeRegion(Request $request): RedirectResponse
    {
        Region::create($this->regionData($request));
        return back()->with('success', 'Région créée avec succès.');
    }

    public function updateRegion(Request $request, Region $region): RedirectResponse
    {
        $region->update($this->regionData($request, $region));
        return back()->with('success', 'Région mise à jour.');
    }

    public function destroyRegion(Region $region): RedirectResponse
    {
        if ($region->villes()->exists()) return back()->with('error', 'Cette région contient des villes et ne peut pas être supprimée.');
        $region->delete();
        return back()->with('success', 'Région supprimée.');
    }

    public function storeVille(Request $request): RedirectResponse
    {
        Ville::create($this->villeData($request));
        return back()->with('success', 'Ville créée avec succès.');
    }

    public function updateVille(Request $request, Ville $ville): RedirectResponse
    {
        $ville->update($this->villeData($request, $ville));
        return back()->with('success', 'Ville mise à jour.');
    }

    public function destroyVille(Ville $ville): RedirectResponse
    {
        foreach (['agences', 'proprietaire', 'locataire'] as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'ville_id') && DB::table($table)->where('ville_id', $ville->id)->exists()) {
                return back()->with('error', 'Cette ville est utilisée dans l’application et ne peut pas être supprimée.');
            }
        }
        $ville->delete();
        return back()->with('success', 'Ville supprimée.');
    }

    private function paysData(Request $request, ?Pays $pays = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150', Rule::unique('pays', 'name')->ignore($pays?->id)],
            'iso2' => ['required', 'string', 'size:2', Rule::unique('pays', 'iso2')->ignore($pays?->id)],
            'indicatif' => ['required', 'string', 'max:8'],
            'actif' => ['required', 'boolean'],
        ]);
        $data['iso2'] = strtoupper($data['iso2']);
        return $data;
    }

    private function regionData(Request $request, ?Region $region = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:150', Rule::unique('regions', 'name')->where(fn ($q) => $q->where('pays_id', $request->pays_id))->ignore($region?->id)],
            'pays_id' => ['required', 'exists:pays,id'],
        ]);
    }

    private function villeData(Request $request, ?Ville $ville = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:150', Rule::unique('villes', 'name')->where(fn ($q) => $q->where('region_id', $request->region_id))->ignore($ville?->id)],
            'region_id' => ['required', 'exists:regions,id'],
        ]);
    }
}
