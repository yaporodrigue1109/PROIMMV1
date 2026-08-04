<?php

namespace App\Http\Controllers\Agence\Reversement;

use App\Http\Controllers\Controller;
use App\Services\Agence\ReversementService;
use App\Services\ProprietaireService;
use App\Models\ProprietaireLot;
use App\Models\TransactionAgence;
use App\Models\Loyer;
use App\Models\ReversementDetail;
use App\Models\Reversement;
use  Illuminate\Support\Facades\DB;
use App\Repositories\Agence\Repository\LotRepository;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Collection;
use Barryvdh\DomPDF\Facade\Pdf;

class ReversementController extends Controller
{
    protected ReversementService $reversementService;
    protected ProprietaireService $proprietaireService;
    protected LotRepository $lotRepo;

    public function __construct(
        ReversementService $reversementService,
        ProprietaireService $proprietaireService,
        LotRepository $lotRepo
    ) {
        $this->reversementService = $reversementService;
        $this->proprietaireService = $proprietaireService;
        $this->lotRepo = $lotRepo;
    }

    /**
     * Afficher la page des reversements
     * Aucune donnée n'est chargée tant qu'une recherche
     * (propriétaire OU période) n'a pas été effectuée.
     */
    public function index(Request $request)
    {
        $agenceId = $this->agenceId();

        $proprietaires = $this->proprietaireService->getAllProprietaireByAgence($agenceId);

        $proprietaireId = $request->input('proprietaire');
        $dateDebut = $request->input('date_debut');
        $dateFin = $request->input('date_fin');

        // La recherche est valide si on a un propriétaire OU une période complète
        $hasSearched = !empty($proprietaireId) || (!empty($dateDebut) && !empty($dateFin));

        $cours = collect();

        if ($hasSearched) {
            // Si une seule des deux dates est fournie (ou aucune), on retombe sur le mois en cours
            $periodeDebut = $dateDebut ?: now()->startOfMonth()->format('Y-m-d');
            $periodeFin = $dateFin ?: now()->endOfMonth()->format('Y-m-d');

            $lots = $this->getLotsWithData($agenceId, $proprietaireId);
            $cours = $this->transformLotsForView($lots, $proprietaires, $periodeDebut, $periodeFin);
        }

        $statistics = $this->reversementService->getStatistics($agenceId);

        $proprietairesFormatted = $proprietaires->map(function ($proprietaire) {
            return [
                'id' => $proprietaire->proprietaire_id,
                'nom' => $proprietaire->name ?? $proprietaire->nom_complet ?? '—',
                'tel' => $proprietaire->tel1 ?? $proprietaire->telephone ?? '',
            ];
        });

        return Inertia::render('Agence/Reversement/index', [
            'proprietaires' => $proprietairesFormatted,
            'cours' => $cours,
            'filters' => [
                'proprietaire' => $proprietaireId,
                'date_debut' => $dateDebut,
                'date_fin' => $dateFin,
            ],
            'statistics' => $statistics,
            'agenceId' => $agenceId,
            'hasSearched' => $hasSearched,
        ]);
    }

    protected function getLotsWithData(string $agenceId, ?string $proprietaireId = null): Collection
    {
        return ProprietaireLot::withDefaultRelations()
            ->where('agence_id', $agenceId)
            ->when($proprietaireId, function ($query) use ($proprietaireId) {
                return $query->where('proprietaire_id', $proprietaireId);
            })
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Transformer les lots pour la vue avec calculs
     */
    protected function transformLotsForView($lots, $proprietaires, string $dateDebut, string $dateFin): Collection
    {
        $proprietairesIndex = $proprietaires->keyBy('proprietaire_id');

        return $lots->map(function ($lot) use ($proprietairesIndex, $dateDebut, $dateFin) {
            $proprietaire = $proprietairesIndex->get($lot->proprietaire_id);

            $locataires = collect();
            $montantLoyerTotal = 0;

            foreach ($lot->proprietes as $propriete) {
                foreach ($propriete->batiments as $batiment) {
                    foreach ($batiment->portes as $porte) {

                        // Uniquement les portes occupées
                        if (!$porte->is_occupe) {
                            continue;
                        }

                      //  dd($porte->locatairesAgence);

                        if ($porte->locatairesAgence && $porte->locatairesAgence->isNotEmpty()) {
                            foreach ($porte->locatairesAgence as $locataireAgence) {
                                if ($locataireAgence->is_active == 1) {

                                    // Montant attendu = mt_loyer de la porte
                                    $montantLoyer = (int) ($porte->mt_loyer ?? 0);

                                    $finances = $this->calculateFinances(
                                        $locataireAgence->locataire_id,
                                        $montantLoyer,
                                        $dateDebut,
                                        $dateFin
                                    );

                                     $isFirst = TransactionAgence::where('locataire_id', $locataireAgence->locataire_id)
                                        ->where('agence_id', $this->agenceId())
                                        ->where('type_transaction', 'loyer')
                                        ->where('is_reversement', 0)
                                        ->whereBetween('date_transaction', [$dateDebut . ' 00:00:00', $dateFin . ' 23:59:59'])
                                        ->where('is_first', 1)
                                        ->exists();

                                         $cautionPayee  = $isFirst ? ($locataireAgence->loyer_net * $locataireAgence->caution) : 0;
                                         $cautionSodeci = $isFirst ? ($locataireAgence->caution_sodeci + $locataireAgence->caution_cie) : 0;

                                  //       dd( $isFirst);

                                    $locataires->push([
                                        'porte' => $porte->numero_porte,
                                        'nom' => $locataireAgence->locataire->name ?? '—',
                                        'tel' => $locataireAgence->locataire->tel1 ?? '',
                                        'montantLoyer' => $montantLoyer,
                                        'propriete_id' => $locataireAgence->propriete_id ?? null,
                                        'batiment_id'  => $locataireAgence->batiment_id ?? null,
                                        'arrieres' => $finances['arrieres'],
                                        'montantAttendu' => $finances['montant_attendu'],
                                        'montantPaye' => !$isFirst ? $finances['montant_paye'] : $finances['montant_paye'] + $cautionPayee+$cautionSodeci ,
                                        'restant' => $finances['restant'],
                                        'avance' => $finances['avance'],
                                        'loyerPaye' => $finances['loyer_paye'],               // <-- nouveau
                                        'arrierePaye' => $finances['arriere_paye'],
                                        'locataire_id' => $locataireAgence->locataire_id,
                                        'porte_id' => $porte->porte_id,
                                        'dateEntree' => $locataireAgence->date_entree,

                                        // 'cautionPayee' =>$locataireAgence->loyer_net * $locataireAgence->caution,
                                        // 'cautionSodeci' => $locataireAgence->caution_sodeci + $locataireAgence->caution_cie,
                                            'cautionPayee' => $cautionPayee ,
                                            'cautionSodeci' =>$cautionSodeci ,
                                           'isFirst' => $isFirst,
                                           'mois_payer' =>$finances['mois_payer'],
                                           'datePaiement' =>$finances['date_paiement']
                                    ]);

                                    $montantLoyerTotal += $montantLoyer;
                                }
                            }
                        }
                    }
                }
            }

         //   dd($lot,$locataires->toArray());

            return [
                'id' => $lot->propreietaire_lot_id,
                'nom' => $lot->name ?? $lot->designation ?? 'Lot sans nom',
                'proprietaireId' => $lot->proprietaire_id,
                'proprietaire_nom' => $proprietaire->name ??  '—',
                'proprietaire_tel' => $proprietaire->tel1 ??  '',
                'commissionRate' => getparametrageAgence($this->agenceId())->commission/100,
                'periode' => [
                    'debut' => $dateDebut,
                    'fin' => $dateFin,
                ],
                'statut' => $lot->statut ?? 'en_attente',
                'depenses' => $lot->depenses ?? 0,
                'nouvelleCaution' => $lot->nouvelle_caution ?? 0,
                'observation' => $lot->observation ?? '',
                'montant_loyer_total' => $montantLoyerTotal,
                'locataires' => $locataires->toArray(),
                'name_entreprise' => getInfoAgence($this->agenceId())->name,
                'logo_entreprise' =>getparametrageAgence($this->agenceId())->logo
            ];
        })->values();
    }

    /**
     * Calcule le montant payé, l'attendu, le restant et l'avance
     * d'un locataire sur la période sélectionnée.
     *
     * Règles :
     * - montant attendu = mt_loyer de la porte occupée
     * - montant payé    = somme de montant_global_verser dans transaction_agences,
     *                     sur la période, is_reversement = 0, type_transaction = 'loyer'
     * - restant         = attendu - payé, si attendu > payé, sinon 0
     * - avance          = payé - attendu, si payé > attendu, sinon 0
     * - jamais de montant négatif (les deux valeurs sont toujours >= 0)
     */
    // protected function calculateFinances(string $locataireId, int $montantLoyer, string $dateDebut, string $dateFin): array
    // {
    //     $montantPaye = (int) TransactionAgence::where('locataire_id', $locataireId)
    //         ->where('type_transaction', 'loyer')
    //         ->where('agence_id', $this->agenceId())
    //         ->where('is_reversement', 0)
    //         ->whereBetween('date_transaction', [$dateDebut . ' 00:00:00', $dateFin . ' 23:59:59'])
    //         ->sum('montant_global_verser');

    //     $montantAttendu = $montantLoyer;
    //     $difference = $montantAttendu - $montantPaye;

    //     $restant = $difference > 0 ? $difference : 0;
    //     $avance = $difference < 0 ? abs($difference) : 0;

    //     return [
    //         'montant_attendu' => $montantAttendu,
    //         'montant_paye' => $montantPaye,
    //         'restant' => $restant,
    //         'avance' => $avance,
    //     ];
    // }

    protected function calculateFinances(string $locataireId, int $montantLoyer, string $dateDebut, string $dateFin): array
{
    // Dette accumulée avant le début de la période sélectionnée
    $arrieres = (int) Loyer::where('locataire_id', $locataireId)
        ->where('statut', '!=', 'Paiement total')
        ->where('date_limit_paiement', '<', $dateDebut)
        ->sum('montant_restant');

    $transactionsPeriode = TransactionAgence::where('locataire_id', $locataireId)
        ->where('agence_id', $this->agenceId())
        ->where('type_transaction', 'loyer')
        ->where('is_reversement', 0)
        ->whereBetween('date_transaction', [$dateDebut . ' 00:00:00', $dateFin . ' 23:59:59']);

    $loyerPaye   = (int) (clone $transactionsPeriode)->sum('montant_loyer_payer');
    $arrierePaye = (int) (clone $transactionsPeriode)->sum('montant_arriere_payer');
    $avancePaye  = (int) (clone $transactionsPeriode)->sum('montant_avance_payer');
    $moisPaye = (clone $transactionsPeriode)->pluck('mois_payer')->toArray();
    $datePaiement = (clone $transactionsPeriode)->pluck('date_transaction')->sort()->first();
    $mois =[];

    if (!empty($moisPaye)) {
    foreach ($moisPaye as $value) {
        // Decode each JSON string and merge into $mois array
        $decoded = json_decode($value, true);
        if (is_array($decoded)) {
            $mois = array_merge($mois, $decoded);
        }
    }
}


    $montantAttendu = $arrieres + $montantLoyer;
    $totalPaye = $arrierePaye + $loyerPaye + $avancePaye;

    $difference = $montantAttendu - ($arrierePaye + $loyerPaye);
    $restant = $difference > 0 ? $difference : 0;
    $avance = $avancePaye;

    return [
        'arrieres'        => $arrieres,
        'montant_attendu' => $montantAttendu,
        'montant_paye'    => $totalPaye,
        'loyer_paye'      => $loyerPaye,
        'arriere_paye'    => $arrierePaye,
        'avance_paye'     => $avancePaye,
        'restant'         => $restant,
        'avance'          => $avance,
        'mois_payer'      => $mois,
        'date_paiement'   => $datePaiement
    ];
}



public function marquerReverse(Request $request, string $lotId)
{

    $data = $request->validate([
        'proprietaire_id'      => 'required|string',
        'periode_debut'        => 'required|date',
        'periode_fin'          => 'required|date',
        'taux_commission'      => 'required|numeric',
        'total_loyer_encaisse' => 'nullable|numeric',
        'montant_commission' => 'nullable|numeric',
        'total_arriere_paye' => 'nullable|numeric',
        'montant_apres_commission' => 'nullable|numeric',
        'total_loyer_attendu' => 'nullable|numeric',
        'total_loyer_paye ' => 'nullable|numeric',
        'total_restant' => 'nullable|numeric',
        'nouvelle_caution'     =>'nullable|numeric',
        'caution_sodeci'     => 'nullable|numeric',
        'depenses_effectuees'  => 'nullable|numeric',
        'net_a_reverser'       => 'nullable|numeric',
        'observation'          => 'nullable|string',
        'locataires'                    => 'required|array|min:1',
        // 'locataires.*.locataire_id'     => 'required|string',
        // 'locataires.*.porte_id'         => 'required|string',
        // 'locataires.*.propriete_id'     => 'nullable|string',
        // 'locataires.*.batiment_id'      => 'nullable|string',
        // 'locataires.*.date_paiement'    => 'required|string',
        // 'locataires.*.caution_payee'     => 'nullable|numeric',
        // 'locataires.*.mois_payer'        => 'nullable|array',
        // 'locataires.*.montant_loyer'    => 'required|numeric',
        // 'locataires.*.caution_sodeci'    => 'nullable|numeric',
        // 'locataires.*.date_entree'     => 'nullable|numeric',
        // 'locataires.*.arrieres'         => 'nullable|numeric',
        // 'locataires.*.montant_attendu'  => 'required|numeric',
        // 'locataires.*.nouvelle_caution'  => 'nullable|numeric',
        // 'locataires.*.total_paye'          => 'nullable|numeric',
        // 'locataires.*.loyer_paye'       => 'nullable|numeric',
        // 'locataires.*.arriere_paye'     => 'nullable|numeric',
        // 'locataires.*.montant_paye'     => 'required|numeric',
        // 'locataires.*.restant'          => 'nullable|numeric',
    ]);

    $agenceId = $this->agenceId();


    // On ne fait jamais confiance aux totaux envoyés par le client : on les recalcule ici
    $totalAttendu = $totalEncaisse = $totalLoyerPaye = $totalArrierePaye = $totalRestant = 0;
    foreach ($data['locataires'] as $l) {
        $totalAttendu     += $l['montant_attendu'];
        $totalEncaisse    += $l['montant_paye'];
        $totalLoyerPaye   += $l['loyer_paye'] ?? 0;
        $totalArrierePaye += $l['arriere_paye'] ?? 0;
        $totalRestant     += $l['restant'] ?? 0;
    }



    $lot = ProprietaireLot::where('agence_id', $agenceId)->findOrFail($lotId);


    return DB::transaction(function () use ($data, $lotId, $agenceId, $lot, $totalAttendu, $totalEncaisse, $totalLoyerPaye, $totalArrierePaye, $totalRestant) {

        // $tauxCommission         = (float) $data['taux_commission'];
        // $montantCommission      = $totalEncaisse * $tauxCommission;
        // $montantApresCommission = $totalEncaisse - $montantCommission;
        // $nouvelleCaution        = (float) ($data['nouvelle_caution'] ?? 0);
        // $depenses               = (float) ($data['depenses_effectuees'] ?? 0);
        // $netAReverser           = $montantApresCommission + $nouvelleCaution - $depenses;

                $reversement = Reversement::create([
                'lot_id' => $lotId,
                'proprietaire_id' => $data['proprietaire_id'],
                'agence_id' => $agenceId,
                'periode_debut' => $data['periode_debut'],
                'periode_fin' => $data['periode_fin'],
                'total_attendu' => $data['total_loyer_attendu'] ?? 0,
                'total_encaisse' => $data['total_loyer_encaisse'] ?? 0,
                'total_restant' => $data['total_restant'] ?? 0,
                'total_loyer_paye' => $data['total_loyer_paye'] ?? 0,
                'total_arriere_paye' => $data['total_arriere_paye'] ?? 0,
                'taux_commission' => $data['taux_commission'] ?? 0,
                'montant_commission' => $data['montant_commission'] ?? 0,
                'montant_apres_commission' => $data['montant_apres_commission'] ?? 0,
                'nouvelle_caution' => $data['nouvelle_caution'] ?? 0,
                'depenses_effectuees' => $data['depenses_effectuees'] ?? 0,
                'net_a_reverser' => $data['net_a_reverser'] ?? 0,
                'statut' => 'reverse',
                'date_reversement' => now(),
                'mode_paiement' => $data['mode_paiement'] ?? null,
                'reference_paiement' => $data['reference_paiement'] ?? null,
                'numero_cheque' => $data['numero_cheque'] ?? null,
                'observation' => $data['observation'] ?? null,
                'signe_par' => $data['signe_par'] ?? null,
                'date_signature' => $data['date_signature'] ?? null,
            ]);


        $locataireIds = [];

        foreach ($data['locataires'] as $l) {
            ReversementDetail::create([

                'reversement_id'  => $reversement->id_reversement,
                'locataire_id'    => $l['locataire_id'],
                'porte_id'        => $l['porte_id'],
                'agence_id'       => $agenceId,
                'proprietaire_id' => $data['proprietaire_id'],
                'lot_id'          => $lotId,
                'propriete_id'    => $l['propriete_id'] ?? null,
                'batiment_id'     => $l['batiment_id'] ?? null,
                'montant_loyer'   => $l['montant_loyer'],
                'arrieres_init'   => $l['arrieres'] ?? 0,
                'montant_attendu' => $l['montant_attendu'],
                'loyer_paye'      => $l['loyer_paye'] ?? 0,
                'arriere_paye'    => $l['arriere_paye'] ?? 0,
                'total_paye'      => $l['total_paye'],
                'impayes'         => $l['restant'] ?? 0,
                'caution_payee'   => $l['caution_payee'] ?? 0,
                'caution_sodeci'  => $l['caution_sodeci'] ?? 0,
                'date_entree'     => $l['date_entree'] ?? null,
                'date_paiement'   => $l['date_paiement'] ?? null,
                'mois_payer'      => json_encode($l['mois_payer']) ?? null,
                'nouvelle_caution' => $l['nouvelle_caution'] ?? 0,
                'montant_paye'     => $l['montant_paye'],

            ]);




            $locataireIds[] = $l['locataire_id'];
        }

        // Marquer comme reversées toutes les transactions loyer de la période concernée
        TransactionAgence::where('agence_id', $agenceId)
            ->whereIn('locataire_id', $locataireIds)
            ->where('type_transaction', 'loyer')
            ->where('is_reversement', 0)
            ->whereBetween('date_transaction', [$data['periode_debut'] . ' 00:00:00', $data['periode_fin'] . ' 23:59:59'])
            ->update(['is_reversement' => 1,
                'date_reversement' => now(),
                'reversement_by' => $reversement->id_reversement,
                'reversement_by' => $this->usersId()
            ]);

       // $lot->statut = 'reverse';
       // $lot->save();

        return redirect()->back()->with('success', 'Reversement effectué avec succès.');
    });
}

public function historique(Request $request)
{
    $agenceId = $this->agenceId();

    $proprietaireId = $request->input('proprietaire_id');
    $lotId = $request->input('lot_id');

    $paginator = Reversement::with(['proprietaire', 'lot'])
        ->where('agence_id', $agenceId)
        ->when($proprietaireId, fn($q) => $q->where('proprietaire_id', $proprietaireId))
        ->when($lotId, fn($q) => $q->where('lot_id', $lotId))
        ->orderBy('periode_debut', 'desc')
        ->paginate(15)
        ->withQueryString();

    $paginator->getCollection()->transform(function ($r) {
        return [
            'id' => $r->id_reversement,
            'proprietaire_nom' => $r->proprietaire->name ?? '—',
            'lot_nom' => $r->lot->name ?? $r->lot->designation ?? '—',
            'periode_debut' => optional($r->periode_debut)->format('Y-m-d'),
            'periode_fin' => optional($r->periode_fin)->format('Y-m-d'),
            'montant_reverse' => (float) $r->net_a_reverser,
            'montant_arriere' => (float) $r->total_arriere_paye,
            'statut' => $r->statut,
        ];
    });

    $proprietaires = $this->proprietaireService->getAllProprietaireByAgence($agenceId)
        ->map(fn($p) => [
            'id' => $p->proprietaire_id,
            'nom' => $p->name ?? $p->nom_complet ?? '—',
        ])->values();

    $lots = ProprietaireLot::where('agence_id', $agenceId)
        ->get()
        ->map(fn($l) => [
            'id' => $l->propreietaire_lot_id,
            'nom' => $l->name ?? $l->designation ?? 'Lot sans nom',
            'proprietaire_id' => $l->proprietaire_id,
        ])->values();

    return Inertia::render('Agence/Reversement/historique', [
        'reversements' => $paginator,
        'filters' => [
            'proprietaire_id' => $proprietaireId,
            'lot_id' => $lotId,
        ],
        'proprietaires' => $proprietaires,
        'lots' => $lots,
    ]);
}

    public function historiqueDetail(string $id, ?string $debut = null, ?string $fin = null)
    {
        $cour = $this->buildCourArchive($id, $debut, $fin);

        return Inertia::render('Agence/Reversement/historique-detail', [
            'cour' => $cour,
        ]);
    }

    /**
     * Générer le PDF de la fiche archivée
     */
    public function pdfReversement(string $id, ?string $debut = null, ?string $fin = null)
    {

        $cour = $this->buildCourArchive($id, $debut, $fin);

        $pdf = Pdf::loadView('agence.reversement.pdf', ['cour' => $cour])
            ->setPaper('a4', 'landscape');

        $nomFichier = 'reversement-' . str_replace(' ', '_', $cour['nom']) . '.pdf';

        return $pdf->download($nomFichier);
    }


 /**
     * Construit les données d'un reversement archivé, communes
     * à l'affichage Inertia et au PDF.
     */
    protected function buildCourArchive(string $id,?string $debut = null,?string $fin = null): array
    {
        $agenceId = $this->agenceId();

        $reversement = Reversement::with(['proprietaire', 'lot', 'details.locataire', 'details.porte'])
            ->where('agence_id', $agenceId)
            ->when($debut && $fin, function ($query) use ($debut, $fin) {
                $query->whereDate('periode_debut', $debut)
                ->whereDate('periode_fin', $fin);
            })
            ->findOrFail($id);

        $locataires = $reversement->details->map(function ($d) {
            return [
                'porte' => $d->porte->numero_porte ?? '—',
                'nom' => $d->locataire->name ?? '—',
                'tel' => $d->locataire->tel1 ?? '',
                'montantLoyer' => (int) $d->montant_loyer,
                'arrieres' => (int) $d->arrieres_init,
                'montantAttendu' => (int) $d->montant_attendu,
                'loyerPaye' => (int) $d->loyer_paye,
                'arrierePaye' => (int) $d->arriere_paye,
                'totalPaye' => (int) $d->total_paye,
                'avance' => (int) $d->montant_paye,
                'restant' => (int) $d->impayes,
                'locataire_id' => $d->locataire_id,
                'porte_id' => $d->porte_id,
                'dateEntree' => $d->date_entree,
                'cautionPayee' => (int) $d->caution_payee,
                'cautionSodeci' => (int) $d->caution_sodeci,
                'datePaiement' => $d->date_paiement,
                'mois_payer' => json_decode($d->mois_payer, true) ?? [],
                'nouvelleCaution' => (int) $d->nouvelle_caution,
            ];
        })->values();

        return [
            'id' => $reversement->id_reversement,
            'nom' => $reversement->lot->name ?? 'Lot',
            'proprietaireId' => $reversement->proprietaire_id,
            'proprietaire_nom' => $reversement->proprietaire->name ?? '—',
            'proprietaire_tel' => $reversement->proprietaire->tel1 ?? '',
            'commissionRate' => (float) $reversement->taux_commission,
            'periode' => [
                'debut' => optional($reversement->periode_debut)->format('Y-m-d'),
                'fin' => optional($reversement->periode_fin)->format('Y-m-d'),
            ],
            'statut' => $reversement->statut,
            'depenses' => (float) $reversement->depenses_effectuees,
            'nouvelleCaution' => (float) $reversement->nouvelle_caution,
            'observation' => $reversement->observation,
            'locataires' => $locataires,
            'name_entreprise' => getInfoAgence($agenceId)->name,
            'logo_entreprise' => getparametrageAgence($agenceId)->logo,
            'totaux' => [
                'totalAttendu' => (float) $reversement->total_attendu,
                'totalEncaisse' => (float) $reversement->total_encaisse,
                'totalRestant' => (float) $reversement->total_restant,
                'totalLoyerPaye' => (float) $reversement->total_loyer_paye,
                'totalArrierePaye' => (float) $reversement->total_arriere_paye,
                'commission' => (float) $reversement->montant_commission,
                'apresCommission' => (float) $reversement->montant_apres_commission,
                'net' => (float) $reversement->net_a_reverser,
            ],
        ];
    }

    // store(), valider(), annuler(), show(), statistics() restent inchangés

    private function agenceId(): string
    {
        return getInfoAgent()->users->agence_id;
    }
      private function usersId(): string
    {
        return getInfoAgent()->users->id_users;
    }
}
