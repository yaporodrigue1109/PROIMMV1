<?php

namespace App\Http\Controllers\Agence\Proprietaire;

use App\Http\Controllers\Controller;
use App\Http\Requests\Agence\Proprietaire\StoreProprietaireRequest;
use App\Http\Requests\Agence\Proprietaire\UpdateProprietaireRequest;
use App\Services\ProprietaireService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\View\View;
use App\Models\Genre;
use App\Models\Proprietaire;
use App\Models\TypePiece;
use App\Models\Region;
use App\Models\Ville;
use  App\Repositories\Agence\Repository\LotRepository;

class ProprietaireController extends Controller
{
    protected  $proprietaireService;
    protected $lotRepository;
    public function __construct(
        ProprietaireService $proprietaireService,LotRepository $lotRepository
    ) {
        $this->proprietaireService = $proprietaireService;
        $this->lotRepository = $lotRepository;
    }

    /**
     * Liste des propriétaires de l'agence.
     */
    public function index(Request $request): Response
    {
        $agenceId = getInfoAgent()->users->agence_id ?? null;
        $filters = $request->only(['search', 'status']);

        $proprietaires = $this->proprietaireService->getPaginated(
            $agenceId,
            $request->integer('per_page', 15),
            $filters
        );
        $proprietaires->appends(array_filter($filters, fn ($value) => $value !== null && $value !== ''));

        $baseQuery = Proprietaire::query()->whereHas('agences', fn ($q) => $q->where('agence_id', $agenceId));

        return Inertia::render('Agence/Proprietaires/Index', [
            'proprietaires' => $proprietaires,
            'stats' => [
                'total' => (clone $baseQuery)->count(),
                'actifs' => (clone $baseQuery)->whereHas('agences', fn ($q) => $q->where('agence_id', $agenceId)->where('is_active', true))->count(),
                'inactifs' => (clone $baseQuery)->whereHas('agences', fn ($q) => $q->where('agence_id', $agenceId)->where('is_active', false))->count(),
                'ce_mois' => (clone $baseQuery)->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count(),
            ],
            'filters' => [
                'search' => $filters['search'] ?? '',
                'status' => $filters['status'] ?? 'all',
            ],
        ]);
    }

    /**
     * Formulaire de création.
     */
    public function create(): Response
    {
        return Inertia::render('Agence/Proprietaires/Form', [
            'mode' => 'create',
            'proprietaire' => null,
            'genres' => Genre::all(),
            'typePiece' => TypePiece::all(),
            'regions' => Region::all(),
            'villes' => Ville::all(),
        ]);
    }

    /**
     * Enregistre un nouveau propriétaire.
     */
    public function store(StoreProprietaireRequest $request): RedirectResponse
    {
        $agenceId = getInfoAgent()->users->agence_id ;
        $proprietaire = $this->proprietaireService->store($request->validated(), $agenceId);

        return redirect()
            ->route('agence.proprietaire.show', $proprietaire->proprietaire_id)
            ->with('success', 'Propriétaire créé avec succès.');
    }

    /**
     * Affiche le détail d'un propriétaire.
     */
    public function show(string $id): Response
    {
        $agenceId     = getInfoAgent()->users->agence_id ;
        $proprietaire = $this->proprietaireService->getById($id, $agenceId);
        abort_if(is_null($proprietaire), 404, 'Propriétaire introuvable.');

        $proprietaire->loadMissing(['region', 'ville', 'agences', 'typePiece']);
        $lots = $this->lotRepository->getAllByProprietaire($id, $agenceId);
        $regions = Region::orderBy('name')->get(['id', 'name']);
        $villes = Ville::orderBy('name')->get(['id', 'name', 'region_id']);
        $proprietes = \App\Models\Propriete::withDefaultRelations()
            ->where('proprietaire_id', $id)
            ->where('agence_id', $agenceId)
            ->orderByDesc('created_at')
            ->get();

        return Inertia::render('Agence/Proprietaires/Show', [
            'proprietaire' => $proprietaire,
            'liaison' => $proprietaire->agences->first(),
            'lots' => $lots,
            'proprietes' => $proprietes,
            'genres' => Genre::all(),
            'typePiece' => TypePiece::all(),
            'regions' => $regions,
            'villes' => $villes,
        ]);
    }

    /**
     * Formulaire d'édition.
     */
    public function edit(string $id): Response
    {
        $agenceId     = getInfoAgent()->users->agence_id ;
        $proprietaire = $this->proprietaireService->getById($id, $agenceId);

        abort_if(is_null($proprietaire), 404, 'Propriétaire introuvable.');

        return Inertia::render('Agence/Proprietaires/Form', [
            'mode'         => 'edit',
            'proprietaire' => $proprietaire,

            'genres' => Genre::all(),
            'typePiece' => TypePiece::all(),
            'regions' => Region::all(),
            'villes' => Ville::all()
        ]);
    }

    /**
     * Met à jour un propriétaire.
     */
    public function update(UpdateProprietaireRequest $request, string $id): RedirectResponse
    {
        $this->proprietaireService->update($id, $request->validated());

        return redirect()
            ->route('agence.proprietaire.show', $id)
            ->with('success', 'Propriétaire mis à jour avec succès.');
    }

    /**
     * Supprime un propriétaire.
     */
    public function destroy(string $id): RedirectResponse
    {
        $deleted = $this->proprietaireService->destroy($id);

        if (!$deleted) {
            return back()->with('error', 'Ce propriétaire ne peut pas être supprimé tant qu’il a des lots ou des propriétés rattachés.');
        }

        return redirect()
            ->route('agence.proprietaire.index')
            ->with('success', 'Propriétaire supprimé avec succès.');
    }

    /**
     * Active un propriétaire dans l'agence.
     */
    public function activate(string $proprietaireAgenceId): RedirectResponse
    {
        $this->proprietaireService->activate($proprietaireAgenceId);

        return back()->with('success', 'Propriétaire activé avec succès.');
    }

    /**
     * Désactive un propriétaire dans l'agence.
     */
    public function deactivate(string $proprietaireAgenceId): RedirectResponse
    {
        $this->proprietaireService->deactivate($proprietaireAgenceId);

        return back()->with('success', 'Propriétaire désactivé avec succès.');
    }
}
