<?php
namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Propriete;
use App\Models\ProprietaireLot;
use App\Models\Porte;
use App\Models\VenteBien;
use App\Models\LocataireAgence;
use App\Models\Agence;
use App\Models\ContactMessage;
use App\Models\Region;
use App\Models\Ville;
use App\Models\Pays;
use App\Services\AgenceService;
use App\Services\ConfigurationTarifService;
use App\Services\SettingService;
use App\Services\Agence\AgencyDocumentBranding;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;
use Inertia\Response;

class WebController extends Controller
{
    private array $assignedSaleIds = [];
    private ?array $assignedRentalDoorIds = null;
    private ?array $portalAgencyIds = null;

    public function __construct(
        protected ConfigurationTarifService $tarifService,
        protected AgenceService $agenceService,
        protected SettingService $settingService,
        protected AgencyDocumentBranding $documentBranding,
    ) {
    }

    public function home(): Response
    {
        return Inertia::render('Web/Home', [
            'tarifs' => $this->tarifService->getTarifsPublics(),
            'appLinks' => $this->appLinks(),
        ]);
    }
    public function properties(Request $request): Response { return Inertia::render('Web/Properties', ['properties' => $this->listProperties(24), 'mode' => $request->string('mode')->toString()]); }
    public function propertyDetails(string $listing): Response
    {
        [$type, $id] = $this->parseListingId($listing);
        $property = match ($type) {
            'lot' => $this->availableLots()->whereKey($id)->first(),
            'porte' => $this->availableDoors()->whereKey($id)->first(),
            default => $this->availableWholeProperties()->whereKey($id)->first(),
        };

        abort_unless($property, 404);

        return Inertia::render('Web/PropertyDetails', [
            'property' => match ($type) {
                'lot' => $this->mapLot($property, true),
                'porte' => $this->mapDoor($property, true),
                default => $this->mapProperty($property, true),
            },
        ]);
    }
    public function pricing(): Response
    {
        return Inertia::render('Web/Pricing', [
            'tarifs' => $this->tarifService->getTarifsPublics(),
        ]);
    }
    public function about(): Response
    {
        $setting = $this->websiteSetting();

        return Inertia::render('Web/About', [
            'websiteContent' => [
                'story' => $setting?->website_story,
                'missionTitle' => $setting?->website_mission_title,
                'missionText' => $setting?->website_mission_text,
                'commitments' => $setting?->website_commitments,
                'faqs' => $setting?->website_faqs,
            ],
        ]);
    }

    private function appLinks(): array
    {
        $setting = $this->websiteSetting();

        return [
            'owner' => ['android' => $setting?->owner_android_url, 'ios' => $setting?->owner_ios_url],
            'tenant' => ['android' => $setting?->tenant_android_url, 'ios' => $setting?->tenant_ios_url],
        ];
    }

    private function websiteSetting(): ?\App\Models\Configuration
    {
        return Schema::hasTable('configurations')
            ? $this->settingService->getSetting()
            : null;
    }
    public function registration(): Response
    {
        return Inertia::render('Web/Registration', [
            'pays' => Pays::query()->where('actif', true)->orderBy('name')->get(['id', 'name', 'iso2', 'indicatif']),
            'regions' => Region::query()->orderBy('name')->get(['id', 'name', 'pays_id']),
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
            'country_code' => ['required', 'string', 'size:2', 'exists:pays,iso2'],
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

        $pays = Pays::query()->where('iso2', strtoupper($validated['country_code']))->firstOrFail();
        $regionDansPays = Region::query()
            ->whereKey($validated['region'])
            ->where('pays_id', $pays->id)
            ->exists();
        $villeDansRegion = Ville::query()
            ->whereKey($validated['ville_id'])
            ->where('region_id', $validated['region'])
            ->exists();

        if (! $regionDansPays) {
            return back()->withErrors(['region' => 'Cette région ne correspond pas au pays sélectionné.'])->withInput();
        }

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
                ->route('agence.dashboard')
                ->with('success', "L’agence « {$agence->name} » a été créée avec succès.");
        } catch (\Throwable $exception) {
            Log::error('Erreur inscription publique agence', ['message' => $exception->getMessage()]);

            return back()
                ->withInput($request->except(['new_responsable_password', 'new_responsable_password_confirmation']))
                ->with('error', 'La création de votre agence a échoué. Vérifiez les informations puis réessayez.');
        }
    }
    public function contact(): Response { return Inertia::render('Web/Contact'); }

    public function legal(string $document): Response
    {
        $setting = $this->websiteSetting();
        $documents = [
            'politique-confidentialite' => ['title' => 'Politique de confidentialité', 'field' => 'politique_confidentialite'],
            'conditions-generales' => ['title' => 'Conditions générales de service', 'field' => 'condition_generale'],
            'conditions-utilisation' => ['title' => "Conditions générales d’utilisation", 'field' => 'cgu'],
            'mentions-legales' => ['title' => 'Mentions légales', 'field' => 'mention_legale'],
        ];

        abort_unless(isset($documents[$document]), 404);
        $definition = $documents[$document];

        return Inertia::render('Web/Legal', [
            'title' => $definition['title'],
            'content' => $setting?->getAttribute($definition['field']),
        ]);
    }
    public function sendContact(Request $request): RedirectResponse
    {
        $data = $request->validate([
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

        ContactMessage::query()->create(array_merge($data, [
            'status' => 'new',
            'ip_address' => $request->ip(),
            'user_agent' => str($request->userAgent())->limit(500)->toString(),
        ]));

        return back()->with('success', 'Votre message a bien été envoyé. Notre équipe vous répondra rapidement.');
    }
    private function listProperties(int $limit): array
    {
        $properties = $this->availableWholeProperties()->get()->map(fn (Propriete $item) => $this->mapProperty($item));
        $lots = $this->availableLots()->get()->map(fn (ProprietaireLot $item) => $this->mapLot($item));
        $doors = $this->availableDoors()->get()->map(fn (Porte $item) => $this->mapDoor($item));

        return $properties->concat($lots)->concat($doors)
            ->sortByDesc('published_at')->take($limit)->values()
            ->map(fn (array $item) => collect($item)->except('published_at')->all())
            ->all();
    }

    /**
     * Expose le même catalogue filtré que le portail web aux autres interfaces.
     */
    public function catalogProperties(): array
    {
        return $this->listProperties(PHP_INT_MAX);
    }

    public function catalogProperty(string $listing): ?array
    {
        [$type, $id] = $this->parseListingId($listing);
        $property = match ($type) {
            'lot' => $this->availableLots()->whereKey($id)->first(),
            'porte' => $this->availableDoors()->whereKey($id)->first(),
            default => $this->availableWholeProperties()->whereKey($id)->first(),
        };

        if (! $property) {
            return null;
        }

        return match ($type) {
            'lot' => $this->mapLot($property, true),
            'porte' => $this->mapDoor($property, true),
            default => $this->mapProperty($property, true),
        };
    }

    public function tenantPortalAgencyIds(): array
    {
        return $this->eligibleAgencyIds('Portail locataire');
    }

    public function isTenantPortalAgencyEligible(string $agencyId): bool
    {
        return in_array($agencyId, $this->tenantPortalAgencyIds(), true);
    }

    private function availableWholeProperties()
    {
        return Propriete::query()
            ->whereIn('agence_id', $this->portalAgencyIds())
            ->where('is_actif', true)
            ->where('sale_type', 'whole')
            ->whereNotNull('sale_price')
            ->whereNotIn('propriete_id', $this->assignedSaleIds('propriete_id'))
            ->with(['typePropriete', 'agence', 'batiments.portes.typePorte', 'batiments.portes.tarifActif', 'proprieteProximites.proximite'])
            ->latest();
    }

    private function availableLots()
    {
        return ProprietaireLot::query()
            ->whereIn('agence_id', $this->portalAgencyIds())
            ->where('is_for_sale', true)
            ->whereNotNull('sale_price')
            ->whereNotIn('propreietaire_lot_id', $this->assignedSaleIds('lot_id'))
            ->with(['agence', 'ville', 'region'])
            ->latest();
    }

    private function availableDoors()
    {
        return Porte::query()
            ->whereIn('agence_id', $this->portalAgencyIds())
            ->where('is_actif', true)
            ->where('is_occupe', false)
            ->whereNotIn('porte_id', $this->assignedRentalDoorIds())
            ->whereNotIn('porte_id', $this->assignedSaleIds('porte_id'))
            ->whereHas('batiment.propriete', function ($query) {
                $query->where('is_actif', true)
                    ->where('sale_type', '!=', 'whole')
                    ->where(function ($mode) {
                        $mode->where('sale_type', 'by_door')->orWhere('is_allocation', true);
                    });
            })
            ->with(['typePorte', 'tarifActif', 'batiment.propriete.typePropriete', 'batiment.propriete.agence'])
            ->latest();
    }

    /**
     * Charge les identifiants attribués séparément pour éviter une comparaison
     * colonne-à-colonne entre les collations historiques des deux tables.
     */
    private function assignedSaleIds(string $column): array
    {
        return $this->assignedSaleIds[$column] ??= VenteBien::query()
            ->whereNotNull($column)
            ->where('statut', '!=', VenteBien::STATUT_ANNULE)
            ->pluck($column)
            ->filter()
            ->values()
            ->all();
    }

    private function assignedRentalDoorIds(): array
    {
        return $this->assignedRentalDoorIds ??= LocataireAgence::query()
            ->where('is_active', true)
            ->whereNotNull('porte_id')
            ->pluck('porte_id')
            ->filter()
            ->values()
            ->all();
    }

    /**
     * Seules les agences actives, avec un abonnement en cours contenant le
     * module supplémentaire « Portail web » actif, peuvent publier des biens.
     */
    private function portalAgencyIds(): array
    {
        if ($this->portalAgencyIds !== null) {
            return $this->portalAgencyIds;
        }

        return $this->portalAgencyIds = $this->eligibleAgencyIds('Portail web');
    }

    private function eligibleAgencyIds(string $featureLabel): array
    {

        $today = now()->startOfDay();

        return Agence::query()
            ->where('statut', 'active')
            ->whereNotNull('abonnement_id')
            ->with('abonnement')
            ->get()
            ->filter(function (Agence $agency) use ($today, $featureLabel) {
                $subscription = $agency->abonnement;

                if (! $subscription || $subscription->statut !== 'actif') {
                    return false;
                }

                if (! $agency->abonnement_end) {
                    return false;
                }

                if ($agency->abonnement_end->startOfDay()->isBefore($today)) {
                    return false;
                }

                return collect($subscription->features ?? [])->contains(function ($feature) use ($featureLabel) {
                    if (! is_array($feature)) {
                        return false;
                    }

                    return mb_strtolower(trim((string) ($feature['label'] ?? ''))) === mb_strtolower($featureLabel)
                        && filter_var($feature['actif'] ?? false, FILTER_VALIDATE_BOOLEAN);
                });
            })
            ->pluck('agence_id')
            ->values()
            ->all();
    }

    private function mapProperty(Propriete $property, bool $withDetails = false): array
    {
        $doors = $property->batiments->flatMap->portes;

        $payload = [
            'id' => 'propriete-'.$property->propriete_id,
            'entity_type' => 'propriete',
            'property_type' => 'Cour',
            'reference' => $property->reference,
            'title' => $property->typePropriete?->name ?? 'Propriété',
            'description' => $property->description,
            'address' => $property->adresse_complete,
            'mode' => 'vente',
            'price' => (float) $property->sale_price,
            'surface' => null,
            'image' => null,
            'buildings_count' => $property->batiments->count(),
            'units_count' => $doors->count(),
            'available_units_count' => 1,
            'published_at' => $property->created_at?->timestamp ?? 0,
        ];

        if (! $withDetails) {
            return $payload;
        }

        return array_merge($payload, [
            'agency' => $this->mapAgency($property->agence),
            'buildings' => $property->batiments->map(fn ($building) => [
                'id' => $building->batiment_id,
                'name' => $building->name ?: 'Bâtiment',
                'description' => $building->description,
                'floors' => $building->nbre_etages,
                'units' => $building->portes
                    ->map(fn ($door) => [
                    'id' => $door->porte_id,
                    'number' => $door->numero_porte,
                    'type' => $door->typePorte?->libelle ?? 'Lot',
                    'description' => $door->description,
                    'surface' => $door->superficie_m2,
                    'floor' => $door->etage,
                    'available' => ! $door->is_occupe,
                    'mode' => $door->is_allocation ? 'location' : 'vente',
                    'price' => (float) ($door->is_allocation
                        ? ($door->tarifActif?->mt_loyer ?? $door->mt_loyer ?? 0)
                        : ($door->tarifActif?->mt_vente ?? 0)),
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

    private function mapLot(ProprietaireLot $lot, bool $withDetails = false): array
    {
        return [
            'id' => 'lot-'.$lot->propreietaire_lot_id,
            'entity_type' => 'lot',
            'property_type' => 'Terrain nu',
            'reference' => $lot->num_lot,
            'title' => filled($lot->name) ? $lot->name : 'Lot'.(filled($lot->num_lot) ? ' '.$lot->num_lot : ''),
            'description' => 'Terrain proposé à la vente.',
            'address' => $lot->adresse,
            'mode' => 'vente',
            'price' => (float) $lot->sale_price,
            'surface' => $lot->superficie,
            'image' => null,
            'buildings_count' => 0,
            'units_count' => 1,
            'available_units_count' => 1,
            'published_at' => $lot->created_at?->timestamp ?? 0,
            'agency' => $withDetails ? $this->mapAgency($lot->agence) : null,
            'buildings' => [],
            'nearby' => [],
            'videos' => [],
        ];
    }

    private function mapDoor(Porte $door, bool $withDetails = false): array
    {
        $property = $door->batiment->propriete;
        $mode = $door->is_allocation ? 'location' : 'vente';
        $price = $mode === 'location'
            ? ($door->tarifActif?->mt_loyer ?? $door->mt_loyer ?? 0)
            : ($door->tarifActif?->mt_vente ?? 0);
        $unit = [
            'id' => $door->porte_id, 'number' => $door->numero_porte,
            'type' => $door->typePorte?->libelle ?? $door->typePorte?->name ?? 'Porte',
            'description' => $door->description, 'surface' => $door->superficie_m2,
            'floor' => $door->etage, 'available' => true, 'mode' => $mode,
            'price' => (float) $price, 'deposit' => (float) ($door->caution ?? 0),
            'advance' => (float) ($door->avance ?? 0),
        ];

        return [
            'id' => 'porte-'.$door->porte_id,
            'entity_type' => 'porte',
            'property_type' => $door->typePorte?->libelle ?? $door->typePorte?->name ?? 'Porte',
            'reference' => $door->numero_porte,
            'title' => $door->typePorte?->libelle ?? $door->typePorte?->name ?? 'Porte disponible',
            'description' => $door->description ?: $property->description,
            'address' => $property->adresse_complete,
            'mode' => $mode,
            'price' => (float) $price,
            'surface' => $door->superficie_m2,
            'image' => null,
            'buildings_count' => 1,
            'units_count' => 1,
            'available_units_count' => 1,
            'published_at' => $door->created_at?->timestamp ?? 0,
            'agency' => $withDetails ? $this->mapAgency($property->agence) : null,
            'buildings' => $withDetails ? [[
                'id' => $door->batiment_id, 'name' => $door->batiment->name ?: 'Bâtiment',
                'description' => $door->batiment->description, 'floors' => $door->batiment->nbre_etages,
                'units' => [$unit],
            ]] : [],
            'nearby' => [],
            'videos' => [],
        ];
    }

    private function mapAgency($agency): array
    {
        $agency?->loadMissing('parametrage');
        $hasConfiguredLogo = filled($agency?->parametrage?->logo);

        return [
            'name' => $agency?->name, 'phone' => $agency?->tel1,
            'email' => $agency?->email1, 'address' => $agency?->adresse,
            'logo_url' => $hasConfiguredLogo
                ? $this->documentBranding->logoUrl($agency)
                : null,
        ];
    }

    private function parseListingId(string $listing): array
    {
        foreach (['propriete', 'porte', 'lot'] as $type) {
            if (str_starts_with($listing, $type.'-')) {
                return [$type, substr($listing, strlen($type) + 1)];
            }
        }

        return ['propriete', $listing];
    }
}
