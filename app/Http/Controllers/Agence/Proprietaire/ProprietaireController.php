<?php

namespace App\Http\Controllers\Agence\Proprietaire;

use App\Http\Controllers\Controller;
use App\Http\Requests\Agence\Proprietaire\StoreProprietaireRequest;
use App\Http\Requests\Agence\Proprietaire\UpdateProprietaireRequest;
use App\Services\ProprietaireService;
use App\Services\Agence\ProprietaireContractDocumentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\View\View;
use App\Models\Genre;
use App\Models\Proprietaire;
use App\Models\TypePiece;
use App\Models\Region;
use App\Models\Ville;
use App\Models\TransactionAgence;
use App\Models\Reversement;
use App\Models\Agence;
use  App\Repositories\Agence\Repository\LotRepository;

class ProprietaireController extends Controller
{
    protected  $proprietaireService;
    protected $lotRepository;
    public function __construct(
        ProprietaireService $proprietaireService,
        LotRepository $lotRepository,
        protected ProprietaireContractDocumentService $contractDocumentService,
    ) {
        $this->proprietaireService = $proprietaireService;
        $this->lotRepository = $lotRepository;
    }

    public function downloadContractDocument(string $id, string $type): HttpResponse
    {
        abort_unless(in_array($type, ['mandat', 'procuration'], true), 404);

        $agenceId = getInfoAgent()->users->agence_id;
        $proprietaire = $this->proprietaireService->getById($id, $agenceId);
        abort_if(is_null($proprietaire), 404, 'Propriétaire introuvable.');

        $proprietaire->loadMissing(['typePiece']);
        $agence = Agence::with(['responsable', 'parametrage', 'ville', 'region'])
            ->whereKey($agenceId)
            ->firstOrFail();
        $lots = $this->lotRepository->getAllByProprietaire($id, $agenceId);
        $proprietes = \App\Models\Propriete::query()
            ->where('agence_id', $agenceId)
            ->where('proprietaire_id', $id)
            ->get();

        $contents = $this->contractDocumentService->generate(
            $type,
            $agence,
            $proprietaire,
            $lots,
            $proprietes
        );
        $filename = $type . '-' . str($proprietaire->name)->slug() . '.pdf';

        return response($contents, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Content-Length' => (string) strlen($contents),
        ]);
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
        $paiements = TransactionAgence::query()
            ->leftJoin('locataire', 'locataire.locataire_id', '=', 'transaction_agences.locataire_id')
            ->leftJoin('propriete', 'propriete.propriete_id', '=', 'transaction_agences.propriete_id')
            ->leftJoin('porte', 'porte.porte_id', '=', 'transaction_agences.porte_id')
            ->leftJoin('mode_paiements', 'mode_paiements.id', '=', 'transaction_agences.mode_paiement_id')
            ->where('transaction_agences.agence_id', $agenceId)
            ->where('transaction_agences.proprietaire_id', $id)
            ->where('transaction_agences.type_transaction', TransactionAgence::STATUT_LOYER)
            ->where('transaction_agences.montant_global_verser', '>', 0)
            ->orderByDesc('transaction_agences.date_transaction')
            ->get([
                'transaction_agences.transaction_agence_id as id',
                'transaction_agences.locataire_id',
                'transaction_agences.date_transaction',
                'transaction_agences.montant_global_verser as montant_paye',
                'locataire.name as locataire',
                'propriete.reference as propriete',
                'porte.numero_porte as porte',
                'mode_paiements.name as mode_paiement',
            ]);
        $locatairesActifs = DB::table('locataire_agence')
            ->join('locataire', 'locataire.locataire_id', '=', 'locataire_agence.locataire_id')
            ->where('locataire_agence.agence_id', $agenceId)
            ->where('locataire_agence.proprietaire_id', $id)
            ->where('locataire_agence.is_active', true)
            ->groupBy('locataire_agence.locataire_id', 'locataire.name')
            ->orderBy('locataire.name')
            ->get([
                'locataire_agence.locataire_id',
                'locataire.name',
                DB::raw('COALESCE(SUM(locataire_agence.loyer_net), 0) as loyer_net'),
            ]);
        $reversementRows = Reversement::query()
            ->with('lot')
            ->where('agence_id', $agenceId)
            ->where('proprietaire_id', $id)
            ->orderByDesc('periode_fin')
            ->get()
            ->map(fn (Reversement $reversement) => [
                'id_reversement' => $reversement->id_reversement,
                'periode_debut' => optional($reversement->periode_debut)->toDateString(),
                'periode_fin' => optional($reversement->periode_fin)->toDateString(),
                'taux_commission' => (float) $reversement->taux_commission,
                'total_loyer' => (float) $reversement->total_encaisse,
                'montant_commission' => (float) $reversement->montant_commission,
                'montant_apres_commission' => (float) $reversement->montant_apres_commission,
                'nouvelle_caution' => (float) $reversement->nouvelle_caution,
                'depenses_effectuees' => (float) $reversement->depenses_effectuees,
                'net_a_reverser' => (float) $reversement->net_a_reverser,
                'statut' => $reversement->statut,
                'lot_id' => $reversement->lot_id,
                'lot' => $reversement->lot?->name ?: 'Lot non renseigné',
                'adresse' => $reversement->lot?->adresse,
            ])
            ->values();

        return Inertia::render('Agence/Proprietaires/Show', [
            'proprietaire' => $proprietaire,
            'liaison' => $proprietaire->agences->first(),
            'lots' => $lots,
            'proprietes' => $proprietes,
            'paiements' => $paiements,
            'locatairesActifs' => $locatairesActifs,
            'reversementRows' => $reversementRows,
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
