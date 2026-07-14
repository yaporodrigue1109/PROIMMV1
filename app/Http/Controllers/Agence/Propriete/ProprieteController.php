<?php

namespace App\Http\Controllers\Agence\Propriete;


use App\Http\Controllers\Controller;
use App\Http\Requests\Agence\Propriete\StoreProprieteRequest;
use App\Http\Requests\Agence\Propriete\UpdateProprieteRequest;
use App\Models\Propriete;
use App\Models\Region;
use App\Models\ProprietaireLot;
use App\Models\Proprietaire;
use App\Models\TypePorte;
use App\Models\TypePropriete;
use App\Models\Ville;
use App\Repositories\Agence\Interfaces\BatimentRepositoryInterface;
use App\Repositories\Agence\Interfaces\PorteRepositoryInterface;
use App\Repositories\Agence\Interfaces\ProprieteRepositoryInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;
use Inertia\Response;
use App\Services\Agence\TypeProprieteService;
use App\Repositories\Agence\Interfaces\TypeProprieteRepositoryInterface;
use App\Repositories\Agence\Interfaces\EquipementProprieteRepositoryInterface;
use App\Repositories\Agence\Interfaces\ProssimiteProprieteRepositoryInterface;
use App\Repositories\Interfaces\ProprietaireRepositoryInterface;
use App\Repositories\Agence\Interfaces\LotRepositoryInterface;
use Illuminate\Support\Collection;

class ProprieteController extends Controller
{
    protected  $proprieteRepo;
        protected   $batimentRepo;
        protected      $porteRepo;
        protected $typePropriete;
        protected $equipementPropriete;
        protected $prossimitePropriete;
        protected $proprietaire;

    protected  $lotRepo;
    public function __construct(
         ProprieteRepositoryInterface $proprieteRepo,
         BatimentRepositoryInterface  $batimentRepo,
         PorteRepositoryInterface     $porteRepo,
         TypeProprieteRepositoryInterface          $typePropriete,
         EquipementProprieteRepositoryInterface  $equipementPropriete,
         ProssimiteProprieteRepositoryInterface $prossimitePropriete,
         ProprietaireRepositoryInterface    $proprietaire,
         LotRepositoryInterface $lotRepo
    ) {
        $this->proprieteRepo = $proprieteRepo;
        $this->batimentRepo = $batimentRepo;
        $this->porteRepo = $porteRepo;
      $this->typePropriete = $typePropriete;
      $this->equipementPropriete = $equipementPropriete;
      $this->prossimitePropriete = $prossimitePropriete;
      $this->proprietaire = $proprietaire;
      $this->lotRepo = $lotRepo;
    }
    // ─────────────────────────────────────────────────────────────
    // INDEX
    // ─────────────────────────────────────────────────────────────

    public function index(Request $request): Response
    {
        $filters = $request->only(['search', 'type_id', 'is_allocation', 'is_actif']);

        $proprietes  = $this->proprieteRepo->paginate($filters, 15)->appends($filters);
        $stats       = $this->proprieteRepo->stats($filters);

        $types = collect();
        $equipements = collect();
        $proximites = collect();

        if (Schema::hasTable((new TypePropriete())->getTable())) {
            $types = $this->typePropriete->getAllByAgence($this->agenceId())->map(function ($type) {
                return [
                    'id' => $type->getKey(),
                    'name' => $type->name,
                    'description' => $type->description,
                ];
            })->values();
        }

        if (Schema::hasTable((new \App\Models\EquipementPropriete())->getTable())) {
            $equipements = $this->equipementPropriete->getAllByAgence($this->agenceId())->map(function ($equipement) {
                return [
                    'id' => $equipement->getKey(),
                    'name' => $equipement->name,
                    'description' => $equipement->description,
                ];
            })->values();
        }

        if (Schema::hasTable((new \App\Models\ProssimitePropriete())->getTable())) {
            $proximites = $this->prossimitePropriete->getAllByAgence($this->agenceId())->map(function ($proximite) {
                return [
                    'id' => $proximite->getKey(),
                    'name' => $proximite->name,
                    'description' => $proximite->description,
                ];
            })->values();
        }

        $proprietes->setCollection(
            $proprietes->getCollection()->map(function ($propriete) {
                return [
                    'id' => $propriete->propriete_id,
                    'reference' => $propriete->reference,
                    'description' => $propriete->description,
                    'adresse_complete' => $propriete->adresse_complete,
                    'is_allocation' => (bool) $propriete->is_allocation,
                    'is_actif' => (bool) $propriete->is_actif,
                    'type' => [
                        'id' => $propriete->typePropriete?->getKey(),
                        'name' => $propriete->typePropriete?->name,
                    ],
                    'proprietaire' => [
                        'id' => $propriete->proprietaire?->getKey(),
                        'name' => $propriete->proprietaire?->name,
                        'tel1' => $propriete->proprietaire?->tel1,
                    ],
                    'batiments_count' => $propriete->nbre_batiment,
                    'portes_total' => $propriete->nbre_porte_total,
                    'portes_libres' => $propriete->nbre_porte_libre,
                    'portes_occupees' => $propriete->nbre_porte_occupe,
                    'progress' => $propriete->nbre_porte_total > 0
                        ? round(($propriete->nbre_porte_occupe / $propriete->nbre_porte_total) * 100)
                        : 0,
                ];
            })->values()
        );

        return Inertia::render('Agence/Proprietes/Index', [
            'proprietes' => $proprietes->toArray(),
            'stats' => $stats,
            'filters' => [
                'search' => $filters['search'] ?? '',
                'type_id' => $filters['type_id'] ?? '',
                'is_allocation' => $filters['is_allocation'] ?? '',
                'is_actif' => $filters['is_actif'] ?? '',
            ],
            'types' => $types,
            'equipements' => $equipements,
            'proximites' => $proximites,
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // CREATE
    // ─────────────────────────────────────────────────────────────

    public function create(Request $request): Response
    {
        $proprietaireId = $request->query('proprietaire_id');
        $lotId = $request->query('lot_id');

        return Inertia::render('Agence/Proprietes/Form', [
            'mode' => 'create',
            ...$this->proprieteFormPayload(),
            'propriete' => $proprietaireId ? [
                'proprietaire_id' => $proprietaireId,
                'lot_id' => $lotId,
                'is_allocation' => true,
                'is_actif' => true,
            ] : null,
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // STORE
    // ─────────────────────────────────────────────────────────────

    public function store(StoreProprieteRequest $request): RedirectResponse
    {
        try {

            DB::transaction(function () use ($request) {
                $data = $request->validated();
                $data['agence_id'] = $this->agenceId();
                $data['adresse_complete'] = $this->lotRepo->findById($data['lot_id'])->adresse;

                $batimentsData = $data['batiments'] ?? [];
                $data['is_allocation'] = $this->resolvePropertyAllocation($batimentsData);
                unset($data['batiments']);

                // 1. Créer la propriété
                $propriete = $this->proprieteRepo->create($data);

                // 2. Créer chaque bâtiment et ses portes
                foreach ($batimentsData as $batimentData) {
                    $portesData = $batimentData['portes'] ?? [];
                    unset($batimentData['portes']);

                    $batiment = $this->batimentRepo->create([
                        ...$batimentData,
                        'propriete_id' => $propriete->propriete_id,
                        'agence_id' => $this->agenceId(),
                    ]);

                    foreach ($portesData as $porteData) {
                        $this->porteRepo->create([
                            ...$porteData,
                            'batiment_id' => $batiment->batiment_id,
                            'agence_id' => $this->agenceId(),
                        ]);
                    }

                }
            });

            return redirect()
                ->route('agence.proprietes.index')
                ->with('success', 'Propriété créée avec succès.');

        } catch (\Throwable $e) {
            return back()
                ->withInput()
                ->with('error', 'Une erreur est survenue : ' . $e->getMessage());
        }
    }

    // ─────────────────────────────────────────────────────────────
    // SHOW
    // ─────────────────────────────────────────────────────────────

    public function show(string $id): Response
    {
        $propriete = $this->proprieteRepo->findById($id);

        abort_if(!$propriete, 404, 'Propriété introuvable.');

        return Inertia::render('Agence/Proprietes/Show', [
            ...$this->proprieteFormPayload(),
            'propriete' => $this->proprieteShowPayload($propriete),
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // EDIT
    // ─────────────────────────────────────────────────────────────

    public function edit(string $id): Response
    {
        $propriete      = $this->proprieteRepo->findById($id);
        abort_if(!$propriete, 404, 'Propriété introuvable.');
        return Inertia::render('Agence/Proprietes/Form', [
            'mode' => 'edit',
            ...$this->proprieteFormPayload($propriete),
        ]);

    }

    // ─────────────────────────────────────────────────────────────
    // UPDATE
    // ─────────────────────────────────────────────────────────────

    public function update(UpdateProprieteRequest $request, string $id): RedirectResponse
    {


        $propriete = $this->proprieteRepo->findById($id);

        abort_if(!$propriete, 404, 'Propriété introuvable.');

        try {
            DB::transaction(function () use ($request, $propriete) {
                $data = $request->validated();
                $data['agence_id'] = $this->agenceId();
                $data['adresse_complete'] = $this->lotRepo->findById($data['lot_id'])->adresse;
                $batimentsData = $data['batiments'] ?? [];
                $data['is_allocation'] = $this->resolvePropertyAllocation($batimentsData);
                unset($data['batiments']);

                // 1. Mettre à jour les infos générales
                $this->proprieteRepo->update($propriete, $data);

                $existingBatiments = $this->batimentRepo->getByPropriete($propriete->propriete_id);
                $incomingBatimentIds = collect($batimentsData)
                    ->pluck('batiment_id')
                    ->filter()
                    ->map(fn($value) => (string) $value)
                    ->all();

                // Supprimer les bâtiments retirés du formulaire
                foreach ($existingBatiments as $existingBatiment) {
                    if (!in_array((string) $existingBatiment->batiment_id, $incomingBatimentIds, true)) {
                        foreach ($existingBatiment->portes as $porte) {
                            $this->porteRepo->delete($porte);
                        }

                        $this->batimentRepo->delete($existingBatiment);
                    }
                }

                foreach ($batimentsData as $batimentData) {
                    $portesData = $batimentData['portes'] ?? [];
                    unset($batimentData['portes']);

                    // 2. Bâtiment existant ou nouveau
                    if (!empty($batimentData['batiment_id'])) {
                        $batiment = $this->batimentRepo->findById($batimentData['batiment_id']);
                        if ($batiment) {
                            $this->batimentRepo->update($batiment, [
                                'name'         => $batimentData['name'],
                                'nbre_etages' => $batimentData['nbre_etages'] ?? 0,
                                'description' => $batimentData['description'] ?? null,
                            ]);
                        }
                    } else {
                        $batiment = $this->batimentRepo->create([
                            ...$batimentData,
                            'propriete_id' => $propriete->propriete_id,
                            'agence_id' => $this->agenceId(),
                        ]);
                    }

                    $incomingPorteIds = collect($portesData)
                        ->pluck('porte_id')
                        ->filter()
                        ->map(fn($value) => (string) $value)
                        ->all();

                    if (!empty($batiment->portes)) {
                        foreach ($batiment->portes as $existingPorte) {
                            if (!in_array((string) $existingPorte->porte_id, $incomingPorteIds, true)) {
                                $this->porteRepo->delete($existingPorte);
                            }
                        }
                    }

                    // 3. Portes
                    foreach ($portesData as $porteData) {
                        $tarifData = $porteData['tarif'] ?? null;
                      //  unset($porteData['tarif']);

                        if (!empty($porteData['porte_id'])) {
                           // dd($porteData);
                            // Porte existante → mise à jour infos physiques
                            $porte = $this->porteRepo->findById($porteData['porte_id']);






                            if ($porte) {
                                $this->porteRepo->update($porte, $porteData);
                              //  dd($porte);
                            }
                        } else {
                            // Nouvelle porte
                            $this->porteRepo->create([
                                ...$porteData,
                                'batiment_id' => $batiment->batiment_id,
                                'tarif'       => $tarifData,
                                'agence_id' => $this->agenceId(),
                            ]);
                        }
                    }
                }
            });

            return redirect()
                ->route('agence.proprietes.show', $propriete->propriete_id)
                ->with('success', 'Propriété mise à jour avec succès.');

        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Une erreur est survenue : ' . $e->getMessage());
        }
    }


    // ─────────────────────────────────────────────────────────────
    // DESTROY
    // ─────────────────────────────────────────────────────────────

    public function destroy(string $id): RedirectResponse
    {
        $propriete = $this->proprieteRepo->findById($id);
        abort_if(!$propriete, 404, 'Propriété introuvable.');

        $this->proprieteRepo->deactivate($propriete);

        return redirect()
            ->route('agence.proprietes.index')
            ->with('success', 'Propriété désactivée.');
    }

    private function agenceId(): string
    {
        return getInfoAgent()->users->agence_id;
    }

    /**
     * @return array<int, array{id:string,name:string,description:string}>
     */
    private function proximiteCatalog(): array
    {
        $proximites = collect();

        if (Schema::hasTable((new \App\Models\ProssimitePropriete())->getTable())) {
            $proximites = $this->prossimitePropriete->getAllByAgence($this->agenceId())->map(function ($proximite) {
                return [
                    'id' => (string) $proximite->getKey(),
                    'name' => $proximite->name,
                    'description' => $proximite->description,
                ];
            })->values();
        }

        return $proximites->all();
    }

    /**
     * @param  array<int, mixed>  $legacyProximites
     * @return array<int, array{id:string,name:string,description:string,distance:?string,unite:?string}>
     */
    private function normalizeSelectedProximites(array $legacyProximites, array $catalog): array
    {
        $catalogMap = collect($catalog)->keyBy(fn ($item) => (string) $item['id']);

        return collect($legacyProximites)
            ->map(function ($item) use ($catalogMap) {
                if (is_string($item) || is_int($item)) {
                    $item = ['id' => $item];
                }

                if (!is_array($item)) {
                    return null;
                }

                $id = (string) ($item['id'] ?? $item['proximite_id'] ?? '');
                if ($id === '') {
                    return null;
                }

                $catalogItem = $catalogMap->get($id, []);

                return [
                    'id' => $id,
                    'name' => $catalogItem['name'] ?? ($item['name'] ?? $id),
                    'description' => $catalogItem['description'] ?? ($item['description'] ?? ''),
                    'distance' => isset($item['distance']) && $item['distance'] !== ''
                        ? (string) (int) round((float) $item['distance'])
                        : null,
                    'unite' => in_array(($item['unite'] ?? null), ['m', 'km'], true) ? $item['unite'] : null,
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @return array<int, array{id:string,name:string,description:string,distance:?string,unite:?string}>
     */
    private function propertyProximitesPayload(Propriete $propriete, array $catalog): array
    {
        $propriete->loadMissing(['proprieteProximites.proximite']);

        $relation = $propriete->proprieteProximites ?? collect();
        if ($relation->count() > 0) {
            $catalogMap = collect($catalog)->keyBy(fn ($item) => (string) $item['id']);

            return $relation->map(function ($item) use ($catalogMap) {
                $catalogItem = $catalogMap->get((string) $item->proximite_id, []);

                return [
                    'id' => (string) $item->proximite_id,
                    'name' => $item->proximite?->name ?? $catalogItem['name'] ?? (string) $item->proximite_id,
                    'description' => $item->proximite?->description ?? $catalogItem['description'] ?? '',
                    'distance' => $item->distance !== null
                        ? (string) (int) round((float) $item->distance)
                        : null,
                    'unite' => $item->unite,
                ];
            })->values()->all();
        }

        $stored = $propriete->prossimites ?? [];
        if (is_string($stored)) {
            $stored = json_decode($stored, true) ?: [];
        }

        if (!is_array($stored)) {
            $stored = [];
        }

        return $this->normalizeSelectedProximites($stored, $catalog);
    }

    /**
     * Derive a property-level allocation flag from its portes.
     */
    private function resolvePropertyAllocation(array $batimentsData): bool
    {
        $porteModes = collect($batimentsData)
            ->flatMap(fn ($batiment) => collect($batiment['portes'] ?? [])->map(fn ($porte) => $porte['is_allocation'] ?? true));

        if ($porteModes->isEmpty()) {
            return true;
        }

        return $porteModes->contains(true);
    }

    private function proprieteFormPayload(?Propriete $propriete = null): array
    {
        $typesPropriete = collect();
        if (Schema::hasTable((new TypePropriete())->getTable())) {
            $typesPropriete = $this->typePropriete->getAllByAgence($this->agenceId())->map(function ($type) {
                return [
                    'id' => $type->getKey(),
                    'name' => $type->name,
                    'description' => $type->description,
                ];
            })->values();
        }

        $regions = collect();
        if (Schema::hasTable((new Region())->getTable())) {
            $regions = Region::orderBy('name')->get()->map(function ($region) {
                return [
                    'id' => $region->getKey(),
                    'name' => $region->name,
                ];
            })->values();
        }

        $villes = collect();
        if (Schema::hasTable((new Ville())->getTable())) {
            $villes = Ville::orderBy('name')->get()->map(function ($ville) {
                return [
                    'id' => $ville->getKey(),
                    'name' => $ville->name,
                    'region_id' => $ville->region_id,
                ];
            })->values();
        }

        $typesPorte = collect();
        if (Schema::hasTable((new TypePorte())->getTable())) {
            $typesPorte = TypePorte::orderBy('type_porte_id')->get()->map(function ($typePorte) {
                return [
                    'id' => $typePorte->getKey(),
                    'name' => $typePorte->libelle,
                    'description' => $typePorte->description,
                ];
            })->values();
        }

        $proprietaires = collect();
        if (Schema::hasTable((new Proprietaire())->getTable())) {
            $proprietaires = $this->proprietaire->getAllByAgence($this->agenceId())->map(function ($proprietaire) {
                return [
                    'id' => $proprietaire->getKey(),
                    'name' => $proprietaire->name,
                    'tel1' => $proprietaire->tel1,
                ];
            })->values();
        }

        $lots = collect();
        if (Schema::hasTable((new ProprietaireLot())->getTable())) {
            $lots = ProprietaireLot::orderBy('name')->get()->map(function ($lot) {
                return [
                    'id' => $lot->getKey(),
                    'name' => $lot->name,
                    'adresse' => $lot->adresse,
                    'proprietaire_id' => $lot->proprietaire_id,
                    'superficie' => $lot->superficie,
                ];
            })->values();
        }

        $equipements = collect();
        if (Schema::hasTable((new \App\Models\EquipementPropriete())->getTable())) {
            $equipements = $this->equipementPropriete->getAllByAgence($this->agenceId())->map(function ($equipement) {
                return [
                    'id' => $equipement->getKey(),
                    'name' => $equipement->name,
                    'description' => $equipement->description,
                ];
            })->values();
        }

        $proximites = collect($this->proximiteCatalog());

        $payload = [
            'propriete' => null,
            'typesPropriete' => $typesPropriete,
            'typesPorte' => $typesPorte,
            'proprietaires' => $proprietaires,
            'lots' => $lots,
            'regions' => $regions,
            'villes' => $villes,
            'equipements' => $equipements,
            'proximites' => $proximites,
        ];

        if ($propriete) {
            $propriete->loadMissing(['batiments.portes.tarifActif', 'batiments.portes', 'lot', 'proprietaire', 'typePropriete']);

            $payload['propriete'] = [
                'id' => $propriete->getKey(),
                'reference' => $propriete->reference,
                'description' => $propriete->description,
                'adresse_complete' => $propriete->adresse_complete,
                'proprietaire_id' => $propriete->proprietaire_id,
                'lot_id' => $propriete->lot_id,
                'type_propriete_id' => $propriete->type_propriete_id,
                'is_allocation' => (bool) $propriete->is_allocation,
                'is_actif' => (bool) $propriete->is_actif,
                'proximites' => $this->propertyProximitesPayload($propriete, $proximites->all()),
                'batiments' => $propriete->batiments->values()->map(function ($batiment) {
                    return [
                        'batiment_id' => $batiment->batiment_id,
                        'name' => $batiment->name,
                        'description' => $batiment->description,
                        'nbre_etages' => $batiment->nbre_etages ?? 0,
                        'portes' => $batiment->portes->values()->map(function ($porte) {
                            $equipements = $porte->equipements ?? [];
                            if (is_string($equipements)) {
                                $equipements = json_decode($equipements, true) ?: [];
                            }

                            $tarif = $porte->tarifActif;

                            return [
                                'porte_id' => $porte->porte_id,
                                'numero_porte' => $porte->numero_porte,
                                'type_porte_id' => $porte->type_porte_id,
                                'superficie_m2' => $porte->superficie_m2,
                                'etage' => $porte->etage,
                                'is_allocation' => (bool) ($porte->is_allocation ?? true),
                                'description' => $porte->description,
                                'is_occupe' => (bool) $porte->is_occupe,
                                'is_actif' => (bool) $porte->is_actif,
                                'equipements' => $equipements,
                                'tarif' => [
                                    'mt_loyer' => $tarif?->mt_loyer ?? '',
                                    'mt_vente' => $tarif?->mt_vente ?? '',
                                    'mt_caution' => $tarif?->mt_caution ?? 0,
                                    'mt_avance' => $tarif?->mt_avance ?? 0,
                                    'mt_frais_agence' => $tarif?->mt_frais_agence ?? 0,
                                    'mt_frais_dossier' => $tarif?->mt_frais_dossier ?? null,
                                    'mt_caution_cie' => $tarif?->mt_caution_cie ?? 0,
                                    'mt_caution_sodeci' => $tarif?->mt_caution_sodeci ?? 0,
                                    'date_effet' => $tarif?->date_effet?->format('Y-m-d') ?? now()->format('Y-m-d'),
                                ],
                            ];
                        })->all(),
                    ];
                })->all(),
            ];
        }

        return $payload;
    }

    private function proprieteShowPayload(Propriete $propriete): array
    {
        $propriete->loadMissing([
            'batiments.portes.typePorte',
            'batiments.portes.tarifActif',
            'lot',
            'proprietaire',
            'typePropriete',
            'agence',
        ]);

        $catalog = $this->proximiteCatalog();
        $decodedProximites = $this->propertyProximitesPayload($propriete, $catalog);

        $batiments = $propriete->batiments->values()->map(function ($batiment) {
            $portes = $batiment->portes->values()->map(function ($porte) {
                $tarif = $porte->tarifActif;
                $equipements = $porte->equipements ?? [];

                if (is_string($equipements)) {
                    $equipements = json_decode($equipements, true) ?: [];
                }

                return [
                    'porte_id' => $porte->porte_id,
                    'numero_porte' => $porte->numero_porte,
                    'type' => [
                        'id' => $porte->typePorte?->getKey(),
                        'name' => $porte->typePorte?->libelle ?? 'N/A',
                    ],
                    'superficie_m2' => $porte->superficie_m2,
                    'etage' => $porte->etage ?? 0,
                    'is_allocation' => (bool) ($porte->is_allocation ?? true),
                    'description' => $porte->description,
                    'is_occupe' => (bool) $porte->is_occupe,
                    'is_actif' => (bool) $porte->is_actif,
                    'equipements' => $equipements,
                    'tarif' => [
                        'mt_loyer' => $tarif?->mt_loyer ?? 0,
                        'mt_vente' => $tarif?->mt_vente ?? 0,
                        'mt_caution' => $tarif?->mt_caution ?? 0,
                        'mt_avance' => $tarif?->mt_avance ?? 0,
                        'mt_frais_agence' => $tarif?->mt_frais_agence ?? 0,
                        'mt_frais_dossier' => $tarif?->mt_frais_dossier ?? null,
                        'mt_caution_cie' => $tarif?->mt_caution_cie ?? 0,
                        'mt_caution_sodeci' => $tarif?->mt_caution_sodeci ?? 0,
                        'date_effet' => $tarif?->date_effet?->format('Y-m-d') ?? null,
                    ],
                ];
            })->all();

            $portesTotal = count($portes);
            $portesOccupees = collect($portes)->where('is_occupe', true)->count();
            $portesLibres = collect($portes)->where('is_occupe', false)->count();

            return [
                'batiment_id' => $batiment->batiment_id,
                'name' => $batiment->name,
                'description' => $batiment->description,
                'nbre_etages' => (int) ($batiment->nbre_etages ?? 0),
                'stats' => [
                    'portes_total' => $portesTotal,
                    'portes_libres' => $portesLibres,
                    'portes_occupees' => $portesOccupees,
                    'occupation_rate' => $portesTotal > 0 ? round(($portesOccupees / $portesTotal) * 100) : 0,
                ],
                'portes' => $portes,
            ];
        })->all();

        $portesTotal = collect($batiments)->sum(fn ($batiment) => $batiment['stats']['portes_total']);
        $portesLibres = collect($batiments)->sum(fn ($batiment) => $batiment['stats']['portes_libres']);
        $portesOccupees = collect($batiments)->sum(fn ($batiment) => $batiment['stats']['portes_occupees']);

        return [
            'id' => $propriete->propriete_id,
            'reference' => $propriete->reference,
            'description' => $propriete->description,
            'adresse_complete' => $propriete->adresse_complete,
            'is_allocation' => (bool) $propriete->is_allocation,
            'is_actif' => (bool) $propriete->is_actif,
            'type' => [
                'id' => $propriete->typePropriete?->getKey(),
                'name' => $propriete->typePropriete?->name ?? 'N/A',
            ],
            'proprietaire' => [
                'id' => $propriete->proprietaire?->getKey(),
                'name' => $propriete->proprietaire?->name ?? 'N/A',
                'tel1' => $propriete->proprietaire?->tel1,
            ],
            'agence' => [
                'id' => $propriete->agence?->getKey(),
                'name' => $propriete->agence?->name ?? 'N/A',
            ],
            'lot' => [
                'id' => $propriete->lot?->getKey(),
                'name' => $propriete->lot?->name ?? 'N/A',
                'adresse' => $propriete->lot?->adresse ?? 'N/A',
            ],
            'proximites' => $decodedProximites,
            'stats' => [
                'batiments_count' => $batiments ? count($batiments) : 0,
                'portes_total' => $portesTotal,
                'portes_libres' => $portesLibres,
                'portes_occupees' => $portesOccupees,
                'occupation_rate' => $portesTotal > 0 ? round(($portesOccupees / $portesTotal) * 100) : 0,
            ],
            'batiments' => $batiments,
        ];
    }

    public function liberer(string $id): RedirectResponse
    {
        $porte = $this->porteRepo->findById($id);

        abort_if(!$porte, 404, 'Porte introuvable.');

        if (!$porte->is_occupe) {
            return back()->with('info', 'Cette porte est déjà libre.');
        }

        $this->porteRepo->liberer($porte);

        $proprieteId = $porte->batiment->propriete->propriete_id;

        return redirect()
            ->route('agence.proprietes.show', $proprieteId)
            ->with('success', "Porte {$porte->numero_porte} libérée avec succès.");
    }

    // ─────────────────────────────────────────────────────────────
    // OCCUPER — is_occupe = true
    // ─────────────────────────────────────────────────────────────

    public function occuper(string $id): RedirectResponse
    {
        $porte = $this->porteRepo->findById($id);

        abort_if(!$porte, 404, 'Porte introuvable.');

        if ($porte->is_occupe) {
            return back()->with('info', 'Cette porte est déjà occupée.');
        }

        $this->porteRepo->occuper($porte);

        $proprieteId = $porte->batiment->propriete->propriete_id;

        return redirect()
            ->route('agence.proprietes.show', $proprieteId)
            ->with('success', "Porte {$porte->numero_porte} marquée comme occupée.");
    }


}

