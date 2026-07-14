<?php

namespace App\Http\Controllers\Agence\Locataire;

use App\Http\Controllers\Controller;
use App\Http\Requests\Agence\StoreLocataireRequest;
use App\Models\Locataire;
use App\Models\Batiment;
use App\Models\Genre;
use App\Models\Porte;
use App\Models\Propriete;
use App\Models\TypePiece;
use App\Models\Region;
use App\Models\PeriodicitePaiement;
use App\Models\Ville;
use App\Models\ModePaiement;
use App\Repositories\Agence\Interfaces\LocataireRepositoryInterface;
use App\Repositories\Interfaces\ProprietaireRepositoryInterface;
use Inertia\Inertia;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use Inertia\Response;

class LocataireController extends Controller
{
    protected  $locataireRepo;
    protected $proprietaireRepo;
    public function __construct(
         LocataireRepositoryInterface $locataireRepo,
         ProprietaireRepositoryInterface $proprietaireRepo
    ) {
        $this->locataireRepo = $locataireRepo;
        $this->proprietaireRepo = $proprietaireRepo;
    }

    // ─────────────────────────────────────────────────────────────
    // INDEX
    // ─────────────────────────────────────────────────────────────

    public function index(Request $request): Response
    {
        $agenceId   = $this->agenceId();
        abort_if(!$agenceId, 403, 'Agence introuvable.');

        $filters    = $request->only(['search', 'is_actif', 'propriete_id']);
        $locataires = $this->locataireRepo->paginate($filters, 15);
        $locataires->appends($filters);
        $stats      = $this->locataireRepo->stats();
        $proprietes = Propriete::withDefaultRelations()
            ->where('agence_id', $agenceId)
            ->where('is_actif', true)
            ->orderBy('reference')
            ->get();


        return Inertia::render('Agence/Locataires/Index', [
            'locataires' => $locataires,
            'stats' => $stats,
            'proprietes' => $proprietes,
            'filters' => [
                'search' => $filters['search'] ?? '',
                'is_actif' => $filters['is_actif'] ?? '',
                'propriete_id' => $filters['propriete_id'] ?? '',
            ],
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // CREATE
    // ─────────────────────────────────────────────────────────────

    public function create(): Response
    {
        $agenceId = $this->agenceId();
        abort_if(!$agenceId, 403, 'Agence introuvable.');

        $proprietes = Propriete::withDefaultRelations()
            ->where('agence_id', $agenceId)
            ->where('is_actif', true)
            ->orderBy('reference')
            ->get();
        $genres = Genre::all();
        $typePiece = TypePiece::all();
        $regions = Region::all();
        $villes = Ville::all();
        $periodicitePaiement = PeriodicitePaiement::query()
            ->where('is_actif', true)
            ->orderBy('name')
            ->get();
        $modePaiement = $this->safeModePaiementOptions();
        $proprio = $this->proprietaireRepo->getAvecPortesDisponiblesByAgence();

        return Inertia::render('Agence/Locataires/Form', [
            'mode' => 'create',
            'locataire' => null,
            'genres' => $genres,
            'typePiece' => $typePiece,
            'regions' => $regions,
            'villes' => $villes,
            'proprio' => $proprio,
            'periodicitePaiement' => $periodicitePaiement,
            'modePaiement' => $modePaiement,
            'proprietes' => $proprietes,
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // STORE
    // ─────────────────────────────────────────────────────────────


    public function store(StoreLocataireRequest $request): RedirectResponse
    {
        try {
            $data = $request->validated();

            // ── Séparer les sous-tableaux ──────────────────────────────────────
            $contratData = $data['contrat'] ?? [];
            $arrieresData = $data['arrieres'] ?? [];
            unset($data['contrat'], $data['arrieres']);

            // ── Upload photos ──────────────────────────────────────────────────
            if ($request->hasFile('photo')) {
                $data['photo'] = upload('locataires/photo', 'png', 'photo', $request);
            }

            if ($request->hasFile('image_pice')) {
                $data['image_pice'] = upload('locataires/image_pice', 'png', 'image_pice', $request);
            }

            // ── Point d'entrée unique (transaction DB incluse dans le repo) ────
            $this->locataireRepo->enregistrer($data, $contratData, $arrieresData);

            return redirect()
                ->route('agence.locataires.index')
                ->with('success', 'Locataire enregistré avec succès.');

        } catch (\RuntimeException $e) {
            // Erreurs métier (ex: locataire déjà existant sur cette porte)
            return back()->withInput()->with('error', $e->getMessage());

        } catch (\Throwable $e) {
            // Erreurs techniques
            return back()->withInput()->with('error', 'Une erreur est survenue : ' . $e->getMessage());
        }
    }

    // ─────────────────────────────────────────────────────────────
    // SHOW
    // ─────────────────────────────────────────────────────────────

    public function show(string $id): Response
    {
        try {
            $locataire = $this->locataireRepo->findById($id);
        } catch (\Throwable $e) {
            $agenceId = $this->agenceId();
            abort_if(!$agenceId, 403, 'Agence introuvable.');

            $locataire = Locataire::with([
                'region',
                'ville',
                'genre',
                'contrats' => fn($q) => $q
                    ->where('agence_id', $agenceId)
                    ->with(['porte.tarifActif', 'propriete', 'proprietaire', 'batiment', 'lot', 'periodicitePaiement', 'modePaiement']),
            ])
                ->whereHas('contrats', fn($q) => $q->where('agence_id', $agenceId))
                ->find($id);
        }
        abort_if(!$locataire, 404);

        return Inertia::render('Agence/Locataires/Show', [
            'locataire' => $locataire,
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // EDIT
    // ─────────────────────────────────────────────────────────────

    public function edit(string $id): Response
    {
        $agenceId = $this->agenceId();
        abort_if(!$agenceId, 403, 'Agence introuvable.');

        $locataire  = $this->locataireRepo->findById($id);
        abort_if(!$locataire, 404);

        $proprietes = Propriete::withDefaultRelations()
            ->where('agence_id', $agenceId)
            ->where('is_actif', true)
            ->orderBy('reference')
            ->get();
        $genres = Genre::all();
        $typePiece = TypePiece::all();
        $regions = Region::all();
        $villes =Ville::all();
        $periodicitePaiement = PeriodicitePaiement::query()
            ->where('is_actif', true)
            ->orderBy('name')
            ->get();
        $modePaiement = $this->safeModePaiementOptions();
        $proprio = $this->proprietaireRepo->getAvecPortesDisponiblesByAgence();

        return Inertia::render('Agence/Locataires/Form', [
            'mode' => 'edit',
            'locataire' => $locataire,
            'genres' => $genres,
            'typePiece' => $typePiece,
            'regions' => $regions,
            'villes' => $villes,
            'proprio' => $proprio,
            'periodicitePaiement' => $periodicitePaiement,
            'modePaiement' => $modePaiement,
            'proprietes' => $proprietes,
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // UPDATE
    // ─────────────────────────────────────────────────────────────

    public function update(StoreLocataireRequest $request, string $id): RedirectResponse
    {
        $locataire = $this->locataireRepo->findById($id);
        abort_if(!$locataire, 404);

        try {
            $data = $request->validated();
            unset($data['contrat']);

            if ($request->hasFile('photo')) {
                if ($locataire->photo) Storage::disk('public')->delete($locataire->photo);
                $data['photo'] = $request->file('photo')->store('locataires/photos', 'public');
            }
            if ($request->hasFile('image_pice')) {
                if ($locataire->image_pice) Storage::disk('public')->delete($locataire->image_pice);
                $data['image_pice'] = $request->file('image_pice')->store('locataires/pieces', 'public');
            }

            dd([
                'id' => $id,
                'locataire' => $locataire->toArray(),
                'validated' => $data,
                'has_photo' => $request->hasFile('photo'),
                'has_image_pice' => $request->hasFile('image_pice'),
            ]);

            $this->locataireRepo->update($locataire, $data);

            return redirect()
                ->route('agence.locataires.show', $id)
                ->with('success', 'Locataire mis à jour.');

        } catch (\Throwable $e) {
            return back()->withInput()->with('error', 'Erreur : ' . $e->getMessage());
        }
    }

    // ─────────────────────────────────────────────────────────────
    // RÉSILIER
    // ─────────────────────────────────────────────────────────────

    public function resilier(string $id): RedirectResponse
    {
        $locataire = $this->locataireRepo->findById($id);
        abort_if(!$locataire, 404);

        $this->locataireRepo->resilierContrat($locataire);

        return redirect()
            ->route('agence.locataires.show', $id)
            ->with('success', 'Contrat résilié. La porte a été libérée.');
    }

    // ─────────────────────────────────────────────────────────────
    // AJAX — portes libres d'une propriété
    // ─────────────────────────────────────────────────────────────

    public function portesLibres(string $proprieteId): \Illuminate\Http\JsonResponse
    {
        $portes = Porte::with(['batiment', 'typePorte', 'tarifActif'])
            ->whereHas('batiment', fn($q) => $q->where('propriete_id', $proprieteId))
            ->where('is_occupe', false)
            ->where('is_actif', true)
            ->get()
            ->map(fn($p) => [
                'id'           => $p->porte_id,
                'label'        => $p->batiment->nom . ' › ' . $p->numero_porte . ' (' . $p->typePorte?->libelle . ')',
                'batiment_id'  => $p->batiment_id,
                'proprietaire' => $p->batiment?->propriete?->proprietaire_id,
                'loyer'        => $p->tarifActif?->mt_loyer ?? 0,
            ]);

        return response()->json($portes);
    }

    private function safeModePaiementOptions()
    {
        if (!Schema::hasTable((new ModePaiement())->getTable())) {
            return collect();
        }

        return ModePaiement::all();
    }

    private function agenceId(): ?string
    {
        return getInfoAgent()?->users?->agence_id ?? auth('user')->user()?->agence_id;
    }
}
