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

    public function home(): Response
    {
        return Inertia::render('Web/Home', [
            'tarifs' => $this->tarifService->getTarifsPublics(),
        ]);
    }
    public function properties(Request $request): Response { return Inertia::render('Web/Properties', ['properties' => $this->listProperties(24), 'mode' => $request->string('mode')->toString()]); }
    public function propertyDetails(Propriete $property): Response
    {
        abort_unless($property->is_actif, 404);

        $property->load([
            'typePropriete',
            'agence',
            'batiments.portes.typePorte',
            'batiments.portes.tarifActif',
            'proprieteProximites.proximite',
        ]);

        return Inertia::render('Web/PropertyDetails', [
            'property' => $this->mapProperty($property, true),
        ]);
    }
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
    public function sendContact(Request $request): RedirectResponse
    {
        $request->validate([
            'request_type' => ['required', 'in:demo,inscription,support,partenariat,autre'],
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150'],
            'phone' => ['nullable', 'string', 'max:20'],
            'subject' => ['required', 'string', 'max:150'],
            'message' => ['required', 'string', 'min:10', 'max:2000'],
        ], [
            'request_type.required' => 'Sélectionnez le motif de votre demande.',
            'name.required' => 'Votre nom est obligatoire.',
            'email.required' => 'Votre adresse email est obligatoire.',
            'email.email' => 'Saisissez une adresse email valide.',
            'subject.required' => 'L’objet de votre demande est obligatoire.',
            'message.required' => 'Votre message est obligatoire.',
            'message.min' => 'Votre message doit contenir au moins 10 caractères.',
        ]);

        return back()->with('success', 'Votre message a bien été envoyé. Notre équipe vous répondra rapidement.');
    }
    private function listProperties(int $limit): array
    {
        return Propriete::query()
            ->where('is_actif', true)
            ->with(['typePropriete', 'batiments.portes.tarifActif'])
            ->latest()
            ->limit($limit)
            ->get()
            ->map(fn (Propriete $property) => $this->mapProperty($property))
            ->all();
    }

    private function mapProperty(Propriete $property, bool $withDetails = false): array
    {
        $doors = $property->batiments->flatMap->portes;
        $availableDoors = $doors->where('is_actif', true)->where('is_occupe', false);
        $referenceDoor = $availableDoors->first() ?? $doors->first();

        $payload = [
            'id' => $property->propriete_id,
            'reference' => $property->reference,
            'title' => $property->typePropriete?->name ?? 'Propriété',
            'description' => $property->description,
            'address' => $property->adresse_complete,
            'mode' => $property->is_allocation ? 'location' : 'vente',
            'price' => (float) ($referenceDoor?->tarifActif?->mt_loyer ?? $referenceDoor?->mt_loyer ?? 0),
            'surface' => $referenceDoor?->superficie_m2,
            'image' => null,
            'buildings_count' => $property->batiments->count(),
            'units_count' => $doors->count(),
            'available_units_count' => $availableDoors->count(),
        ];

        if (! $withDetails) {
            return $payload;
        }

        return array_merge($payload, [
            'agency' => [
                'name' => $property->agence?->name,
                'phone' => $property->agence?->tel1,
                'email' => $property->agence?->email1,
                'address' => $property->agence?->adresse,
            ],
            'buildings' => $property->batiments->map(fn ($building) => [
                'id' => $building->batiment_id,
                'name' => $building->name ?: 'Bâtiment',
                'description' => $building->description,
                'floors' => $building->nbre_etages,
                'units' => $building->portes
                    ->where('is_actif', true)
                    ->where('is_occupe', false)
                    ->map(fn ($door) => [
                    'id' => $door->porte_id,
                    'number' => $door->numero_porte,
                    'type' => $door->typePorte?->libelle ?? 'Lot',
                    'description' => $door->description,
                    'surface' => $door->superficie_m2,
                    'floor' => $door->etage,
                    'available' => true,
                    'price' => (float) ($door->tarifActif?->mt_loyer ?? $door->mt_loyer ?? 0),
                    'deposit' => (float) ($door->caution ?? 0),
                    'advance' => (float) ($door->avance ?? 0),
                ])->values()->all(),
            ])->values()->all(),
            'nearby' => $property->proprieteProximites->map(fn ($item) => [
                'name' => $item->proximite?->libelle ?? $item->proximite?->name,
                'distance' => $item->distance,
                'unit' => $item->unite,
            ])->filter(fn ($item) => filled($item['name']))->values()->all(),
            'videos' => collect($property->videos_url)->filter()->values()->all(),
        ]);
    }
}
