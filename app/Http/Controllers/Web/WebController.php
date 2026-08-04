<?php
namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Propriete;
use App\Models\Region;
use App\Models\Ville;
use App\Services\AgenceService;
use App\Services\ConfigurationTarifService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class WebController extends Controller
{
    public function __construct(
        protected ConfigurationTarifService $tarifService,
        protected AgenceService $agenceService
    ) {
    }

    public function home(): Response { return Inertia::render('Web/Home', ['properties' => $this->listProperties(6)]); }
    public function properties(Request $request): Response { return Inertia::render('Web/Properties', ['properties' => $this->listProperties(24), 'mode' => $request->string('mode')->toString()]); }
    public function pricing(): Response
    {
        return Inertia::render('Web/Pricing', [
            'tarifs' => $this->tarifService->getTarifsPublics(),
        ]);
    }
    public function about(): Response { return Inertia::render('Web/About'); }
    public function registration(): Response
    {
        return Inertia::render('Web/Registration', [
            'regions' => Region::query()->orderBy('name')->get(['id', 'name']),
            'villes' => Ville::query()->orderBy('name')->get(['id', 'name', 'region_id']),
        ]);
    }

    public function registerAgency(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'adresse' => ['required', 'string', 'max:500'],
            'tel1' => ['required', 'string', 'max:20', 'unique:agences,tel1'],
            'email1' => ['required', 'email', 'max:255', 'unique:agences,email1'],
            'region' => ['required', 'exists:regions,id'],
            'ville_id' => ['required', 'exists:villes,id'],
            'new_responsable_name' => ['required', 'string', 'max:255'],
            'new_responsable_email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'new_responsable_tel1' => ['required', 'string', 'max:20'],
            'new_responsable_password' => ['required', 'string', 'min:8', 'confirmed'],
            'accept_terms' => ['accepted'],
        ], [
            'name.required' => 'Le nom de l’agence est obligatoire.',
            'adresse.required' => 'L’adresse de l’agence est obligatoire.',
            'tel1.required' => 'Le téléphone de l’agence est obligatoire.',
            'tel1.unique' => 'Ce téléphone est déjà associé à une agence.',
            'email1.required' => 'L’email de l’agence est obligatoire.',
            'email1.unique' => 'Cet email est déjà associé à une agence.',
            'region.required' => 'Sélectionnez une région.',
            'ville_id.required' => 'Sélectionnez une ville.',
            'new_responsable_name.required' => 'Votre nom complet est obligatoire.',
            'new_responsable_email.required' => 'Votre email de connexion est obligatoire.',
            'new_responsable_email.unique' => 'Un compte utilise déjà cet email.',
            'new_responsable_tel1.required' => 'Votre numéro de téléphone est obligatoire.',
            'new_responsable_password.required' => 'Le mot de passe est obligatoire.',
            'new_responsable_password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
            'new_responsable_password.confirmed' => 'Les mots de passe ne correspondent pas.',
            'accept_terms.accepted' => 'Vous devez accepter les conditions d’utilisation.',
        ]);

        $villeDansRegion = Ville::query()
            ->whereKey($validated['ville_id'])
            ->where('region_id', $validated['region'])
            ->exists();

        if (! $villeDansRegion) {
            return back()->withErrors(['ville_id' => 'Cette ville ne correspond pas à la région sélectionnée.'])->withInput();
        }

        try {
            $agence = $this->agenceService->createAgence(array_merge($validated, [
                'statut' => 'en_demo',
                'responsable_mode' => 'new',
                'tel2' => null,
                'email2' => null,
                'new_responsable_tel2' => null,
                'new_responsable_adresse' => $validated['adresse'],
            ]));

            Auth::guard('user')->login($agence->responsable);
            $request->session()->regenerate();

            return redirect()
                ->route('agence.abonnement.index')
                ->with('success', "L’agence « {$agence->name} » a été créée. Choisissez maintenant votre abonnement.");
        } catch (\Throwable $exception) {
            Log::error('Erreur inscription publique agence', ['message' => $exception->getMessage()]);

            return back()
                ->withInput($request->except(['new_responsable_password', 'new_responsable_password_confirmation']))
                ->with('error', 'La création de votre agence a échoué. Vérifiez les informations puis réessayez.');
        }
    }
    public function contact(): Response { return Inertia::render('Web/Contact'); }
    public function sendContact(Request $request) { $request->validate(['name'=>'required|string|max:100','email'=>'required|email','message'=>'required|string|max:2000']); return back()->with('success', 'Votre message a bien été envoyé. Notre équipe vous répondra rapidement.'); }
    private function listProperties(int $limit): array { return Propriete::query()->where('is_actif', true)->with(['typePropriete','batiments.portes.tarifActif'])->latest()->limit($limit)->get()->map(function ($property) { $door=$property->batiments->flatMap->portes->firstWhere('is_occupe', false) ?? $property->batiments->flatMap->portes->first(); return ['id'=>$property->propriete_id,'reference'=>$property->reference,'title'=>$property->typePropriete?->name ?? 'Propriété','address'=>$property->adresse_complete,'mode'=>$property->is_allocation ? 'location' : 'vente','price'=>(float)($door?->tarifActif?->mt_loyer ?? $door?->mt_loyer ?? 0),'surface'=>$door?->superficie_m2,'image'=>null]; })->all(); }
}
