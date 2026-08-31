<?php

namespace App\Http\Controllers\Agence\Locataire;

use App\Http\Controllers\Controller;
use App\Http\Requests\Agence\StoreLocataireRequest;
use App\Models\Locataire;
use App\Models\LocataireAgence;
use App\Models\Loyer;
use App\Models\Agence;
use App\Models\Batiment;
use App\Models\Genre;
use App\Models\Porte;
use App\Models\Propriete;
use App\Models\ProprietaireAgence;
use App\Models\TypePiece;
use App\Models\Region;
use App\Models\PeriodicitePaiement;
use App\Models\Ville;
use App\Models\ModePaiement;
use App\Models\CaisseSession;
use App\Repositories\Agence\Interfaces\LocataireRepositoryInterface;
use App\Repositories\Interfaces\ProprietaireRepositoryInterface;
use App\Services\Agence\LocataireContractDocumentService;
use App\Services\Agence\AgencyDocumentBranding;
use App\Services\Agence\CaisseClotureService;
use Inertia\Inertia;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use Inertia\Response;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Http\JsonResponse;

class LocataireController extends Controller
{
    protected  $locataireRepo;
    protected $proprietaireRepo;
    public function __construct(
         LocataireRepositoryInterface $locataireRepo,
         ProprietaireRepositoryInterface $proprietaireRepo,
         protected LocataireContractDocumentService $contractDocumentService,
         protected AgencyDocumentBranding $documentBranding,
         protected CaisseClotureService $caisseClotureService,
    ) {
        $this->locataireRepo = $locataireRepo;
        $this->proprietaireRepo = $proprietaireRepo;
    }

    public function downloadContractDocument(string $locataire, string $type): HttpResponse
    {
        abort_unless(in_array($type, LocataireContractDocumentService::TYPES, true), 404);

        $agenceId = $this->agenceId();
        abort_if(!$agenceId, 403, 'Agence introuvable.');

        $tenant = Locataire::query()
            ->whereHas('contrats', fn ($query) => $query->where('agence_id', $agenceId))
            ->findOrFail($locataire);
        $contract = LocataireAgence::query()
            ->with([
                'porte.tarifActif',
                'porte.typePorte',
                'propriete',
                'proprietaire.typePiece',
                'batiment',
                'lot',
            ])
            ->where('agence_id', $agenceId)
            ->where('locataire_id', $tenant->locataire_id)
            ->where('is_active', true)
            ->latest('date_debut_bail')
            ->first();
        abort_if(!$contract, 422, 'Aucun contrat actif disponible pour générer ce document.');

        $agence = Agence::with(['responsable', 'parametrage'])->findOrFail($agenceId);
        $impayes = (float) Loyer::query()
            ->where('agence_id', $agenceId)
            ->where('locataire_id', $tenant->locataire_id)
            ->where('porte_id', $contract->porte_id)
            ->whereIn('statut', [Loyer::STATUT_IMPAYE, Loyer::STATUT_PARTIEL])
            ->sum('montant_restant');

        $contents = $this->contractDocumentService->generate($type, $agence, $tenant, $contract, $impayes);
        $filename = $type . '-' . str($tenant->name)->slug() . '.pdf';

        return response($contents, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Content-Length' => (string) strlen($contents),
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // INDEX
    // ─────────────────────────────────────────────────────────────

    public function index(Request $request): Response
    {
        $agenceId   = $this->agenceId();
        abort_if(!$agenceId, 403, 'Agence introuvable.');

        $filters    = $request->only(['search', 'propriete_id']);
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
                'propriete_id' => $filters['propriete_id'] ?? '',
            ],
        ]);
    }

    public function impayes(Request $request): Response
    {
        $search = trim((string) $request->input('search', ''));
        $proprietaireId = (string) $request->input('proprietaire_id', '');
        $rows = $this->impayesQuery($search, $proprietaireId)->paginate(20)->withQueryString();
        $proprietaires = ProprietaireAgence::with('proprietaire')->where('agence_id', $this->agenceId())->where('is_active', true)->get()
            ->map(fn ($item) => ['id' => $item->proprietaire_id, 'name' => $item->proprietaire?->name ?: 'Propriétaire'])->unique('id')->sortBy('name')->values();

        return Inertia::render('Agence/Locataires/Impayes', [
            'locataires' => $rows,
            'filters' => ['search' => $search, 'proprietaire_id' => $proprietaireId],
            'proprietaires' => $proprietaires,
            'total' => (float) $this->impayesQuery($search, $proprietaireId)->get()->sum('montant_impaye'),
        ]);
    }

    public function detailImpayes(string $locataire): Response
    {
        $agenceId = $this->agenceId();
        $tenant = Locataire::whereHas('contrats', fn ($q) => $q->where('agence_id', $agenceId)->where('is_active', true))->findOrFail($locataire);
        $loyers = Loyer::where('agence_id', $agenceId)->where('locataire_id', $tenant->getKey())
            ->whereRaw('montant_a_payer > COALESCE(montant_payer, 0)')
            ->with(['porte', 'propriete'])->orderByDesc('annee_paiement')->orderByDesc('mois_paiement')->get()
            ->map(fn (Loyer $loyer) => [
                'id' => $loyer->getKey(), 'periode' => $loyer->periode,
                'propriete' => $loyer->propriete?->reference ?: '—', 'porte' => $loyer->porte?->numero_porte ?: '—',
                'attendu' => (float) $loyer->montant_a_payer, 'paye' => (float) $loyer->montant_payer,
                'reste' => max((float) $loyer->montant_a_payer - (float) $loyer->montant_payer, 0), 'statut' => $loyer->statut,
            ])->filter(fn ($row) => $row['reste'] > 0)->values();

        return Inertia::render('Agence/Locataires/ImpayesDetail', [
            'locataire' => ['id' => $tenant->getKey(), 'name' => $tenant->name, 'code' => $tenant->code, 'tel1' => $tenant->tel1],
            'loyers' => $loyers, 'total' => (float) $loyers->sum('reste'),
        ]);
    }

    public function impayesPdf(Request $request): HttpResponse
    {
        $agenceId = $this->agenceId(); $agence = Agence::find($agenceId);
        $rows = $this->impayesQuery(trim((string) $request->input('search', '')), (string) $request->input('proprietaire_id', ''))->get();
        $pdf = new \FPDF('P', 'mm', 'A4'); $pdf->SetMargins(12, 10, 12); $pdf->AddPage();
        if ($logo = $this->documentBranding->localLogoPath($agence)) { try { $pdf->Image($logo, 12, 10, 24, 24); } catch (\Throwable) {} }
        $text = fn ($v) => iconv('UTF-8', 'windows-1252//TRANSLIT//IGNORE', (string) $v) ?: (string) $v;
        $pdf->SetXY(41, 12); $pdf->SetFont('Arial', 'B', 14); $pdf->SetTextColor(0, 85, 155); $pdf->Cell(0, 7, $text($agence?->name ?: 'Agence immobiliere'), 0, 1);
        $pdf->SetY(40); $pdf->SetFont('Arial', 'B', 16); $pdf->Cell(0, 9, 'LISTE DES LOCATAIRES EN IMPAYE', 0, 1, 'C'); $pdf->Ln(4);
        $widths = [70, 28, 32, 26, 38]; $labels = ['Locataire', 'Code', 'Telephone', 'Echeances', 'Montant impaye'];
        $pdf->SetFillColor(0, 85, 155); $pdf->SetTextColor(255); $pdf->SetFont('Arial', 'B', 8); foreach ($labels as $i => $label) $pdf->Cell($widths[$i], 8, $text($label), 1, 0, 'C', true); $pdf->Ln();
        $pdf->SetTextColor(30, 41, 59); $pdf->SetFont('Arial', '', 8);
        foreach ($rows as $row) { foreach ([$row->name, $row->code, $row->tel1, $row->impayes_count, number_format((float) $row->montant_impaye, 0, ',', ' ').' FCFA'] as $i => $value) $pdf->Cell($widths[$i], 7, $text($value), 1); $pdf->Ln(); }
        $pdf->Ln(4); $pdf->SetFont('Arial', 'B', 10); $pdf->Cell(0, 8, $text('TOTAL : '.number_format((float) $rows->sum('montant_impaye'), 0, ',', ' ').' FCFA'), 0, 1, 'R');
        $contents = $pdf->Output('S');
        return response($contents, 200, ['Content-Type' => 'application/pdf', 'Content-Disposition' => 'inline; filename="locataires-impayes.pdf"']);
    }

    private function impayesQuery(string $search = '', string $proprietaireId = '')
    {
        $agenceId = $this->agenceId();
        $due = fn ($q) => $q->where('agence_id', $agenceId)->when($proprietaireId !== '', fn ($q) => $q->where('proprietaire_id', $proprietaireId))->whereRaw('montant_a_payer > COALESCE(montant_payer, 0)');
        return Locataire::query()->whereHas('contrats', fn ($q) => $q->where('agence_id', $agenceId)->where('is_active', true))
            ->whereHas('loyers', $due)->withCount(['loyers as impayes_count' => $due])
            ->addSelect(['montant_impaye' => Loyer::query()->selectRaw('COALESCE(SUM(GREATEST(montant_a_payer - COALESCE(montant_payer, 0), 0)), 0)')->whereColumn('loyer.locataire_id', 'locataire.locataire_id')->where('agence_id', $agenceId)->when($proprietaireId !== '', fn ($q) => $q->where('proprietaire_id', $proprietaireId))->whereRaw('montant_a_payer > COALESCE(montant_payer, 0)')])
            ->when($search !== '', fn ($q) => $q->search($search))->orderByDesc('montant_impaye');
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
            ->where('sale_type', '!=', 'whole')
            ->whereHas('lot', fn ($query) => $query->where('is_for_sale', false))
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
        $proprio = $this->rentalOwnersWithAvailableDoors();

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
        $agenceId = $this->agenceId();
        $this->caisseClotureService->cloturerCaissesExpirees($agenceId);
        if (! CaisseSession::where('agence_id', $agenceId)->whereNull('closed_at')->exists()) {
            return back()->withInput()->with('error', 'La caisse doit être ouverte avant d’enregistrer un nouveau locataire.');
        }

        try {
            $data = $request->validated();

            // ── Séparer les sous-tableaux ──────────────────────────────────────
            $contratData = $data['contrat'] ?? [];
            $this->ensureDoorCanBeRented($contratData['porte_id'] ?? null, $agenceId);
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

    public function caisseStatus(): JsonResponse
    {
        $agenceId = $this->agenceId();
        abort_if(! $agenceId, 403, 'Agence introuvable.');
        $this->caisseClotureService->cloturerCaissesExpirees($agenceId);

        return response()->json([
            'open' => CaisseSession::where('agence_id', $agenceId)->whereNull('closed_at')->exists(),
        ]);
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
                $data['photo'] = update('locataires/photo', $locataire->photo, 'png', $request, 'photo');
            }
            if ($request->hasFile('image_pice')) {
                $data['image_pice'] = update('locataires/pieces', $locataire->image_pice, 'png', $request, 'image_pice');
            }

            // dd([
            //     'id' => $id,
            //     'locataire' => $locataire->toArray(),
            //     'validated' => $data,
            //     'has_photo' => $request->hasFile('photo'),
            //     'has_image_pice' => $request->hasFile('image_pice'),
            // ]);

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
            ->route('agence.locataires.index')
            ->with('success', 'Locataire désactivé. Ses portes ont été libérées et son accès mobile a été mis à jour.');
    }

    // ─────────────────────────────────────────────────────────────
    // AJAX — portes libres d'une propriété
    // ─────────────────────────────────────────────────────────────

    public function portesLibres(string $proprieteId): \Illuminate\Http\JsonResponse
    {
        $portes = Porte::with(['batiment', 'typePorte', 'tarifActif'])
            ->whereHas('batiment.propriete', fn($q) => $q
                ->where('propriete_id', $proprieteId)
                ->where('sale_type', '!=', 'whole')
                ->whereHas('lot', fn ($lotQuery) => $lotQuery->where('is_for_sale', false)))
            ->where('is_occupe', false)
            ->where('is_actif', true)
            ->where(fn ($query) => $query->where('is_allocation', true)->orWhereNull('is_allocation'))
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

    private function rentalOwnersWithAvailableDoors()
    {
        return $this->proprietaireRepo->getAvecPortesDisponiblesByAgence()
            ->map(function ($owner) {
                $lots = $owner->lots
                    ->reject(fn ($lot) => (bool) $lot->is_for_sale)
                    ->map(function ($lot) {
                        $properties = $lot->proprietes
                            ->filter(fn ($property) => ($property->sale_type ?? 'none') !== 'whole')
                            ->map(function ($property) {
                                $buildings = $property->batiments->map(function ($building) {
                                    $building->setRelation('portes', $building->portes
                                        ->filter(fn ($door) => $door->is_actif && ! $door->is_occupe && $door->is_allocation !== false)
                                        ->values());
                                    return $building;
                                })->filter(fn ($building) => $building->portes->isNotEmpty())->values();
                                $property->setRelation('batiments', $buildings);
                                return $property;
                            })->filter(fn ($property) => $property->batiments->isNotEmpty())->values();
                        $lot->setRelation('proprietes', $properties);
                        return $lot;
                    })->filter(fn ($lot) => $lot->proprietes->isNotEmpty())->values();
                $owner->setRelation('lots', $lots);
                return $owner;
            })
            ->filter(fn ($owner) => $owner->lots->isNotEmpty())
            ->values();
    }

    private function ensureDoorCanBeRented(?string $doorId, string $agenceId): void
    {
        if (! $doorId) {
            return;
        }

        $door = Porte::with('batiment.propriete.lot')
            ->where('agence_id', $agenceId)
            ->findOrFail($doorId);
        $property = $door->batiment?->propriete;
        $lot = $property?->lot;

        if ((bool) $lot?->is_for_sale
            || ($property?->sale_type ?? 'none') === 'whole'
            || $door->is_allocation === false) {
            throw new \RuntimeException('Ce lot, cette propriété ou cette porte est mis en vente et ne peut pas être attribué à un locataire.');
        }
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
