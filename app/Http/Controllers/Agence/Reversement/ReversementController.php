<?php

namespace App\Http\Controllers\Agence\Reversement;

use App\Http\Controllers\Controller;
use App\Services\Agence\ReversementService;
use App\Services\Agence\ReversementPdfService;
use App\Services\Agence\AgencyDocumentBranding;
use App\Services\ProprietaireService;
use App\Models\ProprietaireLot;
use App\Models\TransactionAgence;
use App\Models\Loyer;
use App\Models\Maintenance;
use App\Models\VenteBien;
use App\Models\ReversementDetail;
use App\Models\Reversement;
use  Illuminate\Support\Facades\DB;
use App\Repositories\Agence\Repository\LotRepository;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Collection;

class ReversementController extends Controller
{
    protected ReversementService $reversementService;
    protected ProprietaireService $proprietaireService;
    protected LotRepository $lotRepo;
    protected ReversementPdfService $pdfService;

    public function __construct(
        ReversementService $reversementService,
        ProprietaireService $proprietaireService,
        LotRepository $lotRepo,
        ReversementPdfService $pdfService,
        protected AgencyDocumentBranding $documentBranding,
    ) {
        $this->reversementService = $reversementService;
        $this->proprietaireService = $proprietaireService;
        $this->lotRepo = $lotRepo;
        $this->pdfService = $pdfService;
    }

    /**
     * Afficher la page des reversements
     * Aucune donnée n'est chargée tant qu'une recherche
     * (propriétaire OU période) n'a pas été effectuée.
     */
    public function index(Request $request)
    {
        $agenceId = $this->agenceId();

        $proprietaires = $this->proprietaireService->getPaginated($agenceId);

        $proprietaireId = $request->input('proprietaire');
        $dateDebut = $request->input('date_debut', now()->startOfMonth()->format('Y-m-d'));
        $dateFin = $request->input('date_fin', now()->endOfMonth()->format('Y-m-d'));

        // La recherche est valide si on a un propriétaire OU une période complète
        $hasSearched = !empty($proprietaireId) || (!empty($dateDebut) && !empty($dateFin));

        $cours = collect();

        if ($hasSearched) {
            // Si une seule des deux dates est fournie, on complète avec le mois en cours.
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
            $montantMaintenances = $this->maintenanceAmountForLot(
                (string) $lot->propreietaire_lot_id,
                $dateDebut,
                $dateFin
            );
            $vente = VenteBien::query()
                ->with('acheteur')
                ->where('agence_id', $this->agenceId())
                ->where('lot_id', $lot->propreietaire_lot_id)
                ->where('proprietaire_id', $lot->proprietaire_id)
                ->where('statut', '!=', VenteBien::STATUT_ANNULE)
                ->latest('created_at')
                ->first();

            $venteData = null;
            if ($vente) {
                $tousLesVersements = TransactionAgence::query()
                    ->where('agence_id', $this->agenceId())
                    ->where('type_transaction', TransactionAgence::STATUT_VENTE)
                    ->where('reference', $vente->getKey())
                    ->orderBy('date_transaction')
                    ->get();
                $versementsPeriode = $tousLesVersements
                    ->filter(fn ($transaction) => ! $transaction->is_reversement
                        && $transaction->date_transaction
                        && $transaction->date_transaction->between(
                            $dateDebut.' 00:00:00',
                            $dateFin.' 23:59:59'
                        ));
                $totalVerse = (float) $tousLesVersements->sum('montant_global_verser');
                $venteSoldee = $totalVerse >= (float) $vente->prix_vente;
                $tousLesVersementsReverses = $tousLesVersements->isNotEmpty()
                    && $tousLesVersements->every(fn ($transaction) => (bool) $transaction->is_reversement);

                if ($venteSoldee && $tousLesVersementsReverses) {
                    return null;
                }
                $tauxAgence = (float) $vente->prix_vente > 0 && (float) $vente->commission > 0
                    ? ((float) $vente->commission / (float) $vente->prix_vente) * 100
                    : (float) (getparametrageAgence($this->agenceId())->commission ?? 0);
                $commissionPeriode = (float) $versementsPeriode->sum('montant_global_verser') * ($tauxAgence / 100);
                $versementsPeriodeIds = $versementsPeriode->pluck('transaction_agence_id')->flip();
                $cumulVerse = 0;
                $versementsData = $tousLesVersements->map(function ($transaction) use (&$cumulVerse, $versementsPeriodeIds, $vente, $tauxAgence) {
                    $montant = (float) $transaction->montant_global_verser;
                    $cumulVerse += $montant;
                    if (! $versementsPeriodeIds->has($transaction->getKey())) {
                        return null;
                    }
                    $commission = $montant * ($tauxAgence / 100);
                    return [
                        'id' => (string) $transaction->getKey(),
                        'date' => optional($transaction->date_transaction)->format('Y-m-d H:i:s'),
                        'montant' => $montant,
                        'tauxAgence' => $tauxAgence,
                        'commissionAgence' => $commission,
                        'netApresCommission' => $montant - $commission,
                        'resteApresVersement' => max((float) $vente->prix_vente - $cumulVerse, 0),
                        'numeroRecu' => $transaction->numero_recu,
                    ];
                })->filter()->values();

                $venteData = [
                    'id' => (string) $vente->getKey(),
                    'reference' => $vente->reference,
                    'dateAccord' => optional($vente->date_accord)->format('Y-m-d'),
                    'prixVente' => (float) $vente->prix_vente,
                    'montantVersePeriode' => (float) $versementsPeriode->sum('montant_global_verser'),
                    'totalVerse' => $totalVerse,
                    'montantRestant' => max((float) $vente->prix_vente - $totalVerse, 0),
                    'tauxAgence' => $tauxAgence,
                    'commissionAgencePeriode' => $commissionPeriode,
                    'netProprietairePeriode' => (float) $versementsPeriode->sum('montant_global_verser') - $commissionPeriode,
                    'statut' => $vente->statut,
                    'typePaiement' => $vente->type_paiement,
                    'acheteur' => [
                        'nom' => $vente->acheteur?->name ?? '—',
                        'telephone' => $vente->acheteur?->telephone1 ?? '',
                        'email' => $vente->acheteur?->email ?? '',
                    ],
                    'versements' => $versementsData->all(),
                ];
            }

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
                                         $fraisDossier = $isFirst ? (float) ($locataireAgence->frais_de_dossier ?? 0) : 0;

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
                                           'fraisDossier' => $fraisDossier,
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
                'ficheType' => $venteData ? 'vente' : 'location',
                'vente' => $venteData,
                'lot' => [
                    'nom' => $lot->name ?? $lot->designation ?? 'Lot sans nom',
                    'numero' => $lot->num_lot ?? '',
                    'ilot' => $lot->num_ilot ?? '',
                    'adresse' => $lot->adresse ?? '',
                ],
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
                'montantMaintenances' => $montantMaintenances,
                'cautionSodeci' => $lot->caution_sodeci ?? 0,
                'nouvelleCaution' => $lot->nouvelle_caution ?? 0,
                'observation' => $lot->observation ?? '',
                'montant_loyer_total' => $montantLoyerTotal,
                'locataires' => $locataires->toArray(),
                'name_entreprise' => getInfoAgence($this->agenceId())->name,
                'logo_entreprise' => $this->documentBranding->logoUrl(getInfoAgence($this->agenceId()))
            ];
        })->filter()->values();
    }

    /** Montant payé en caisse pour les maintenances supportées par le propriétaire. */
    protected function maintenanceAmountForLot(string $lotId, string $dateDebut, string $dateFin): float
    {
        $maintenanceIds = Maintenance::query()
            ->where('agence_id', $this->agenceId())
            ->where('lot_id', $lotId)
            ->where('prise_en_charge_par', Maintenance::PRISE_EN_CHARGE_PROPRIETAIRE)
            ->pluck('maintenance_id');

        if ($maintenanceIds->isEmpty()) {
            return 0;
        }

        return (float) TransactionAgence::query()
            ->where('agence_id', $this->agenceId())
            ->where('type_transaction', TransactionAgence::STATUT_MAINTENANCE)
            ->where('is_reversement', 0)
            ->whereIn('reference', $maintenanceIds)
            ->whereBetween('date_transaction', [$dateDebut.' 00:00:00', $dateFin.' 23:59:59'])
            ->sum('montant_global_verser');
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
        'frais_dossier' => 'nullable|numeric|min:0',
        'montant_maintenances' => 'nullable|numeric|min:0',
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
    $totalAttendu = $totalEncaisse = $totalLoyerPaye = $totalArrierePaye = $totalRestant = $totalCautionSodeci = $totalFraisDossier = 0;
    foreach ($data['locataires'] as $l) {
        $totalAttendu     += $l['montant_attendu'];
        $totalEncaisse    += $l['montant_paye'];
        $totalLoyerPaye   += $l['loyer_paye'] ?? 0;
        $totalArrierePaye += $l['arriere_paye'] ?? 0;
        $totalRestant     += $l['restant'] ?? 0;
        $totalCautionSodeci += $l['caution_sodeci'] ?? 0;
        $totalFraisDossier += $l['frais_dossier'] ?? 0;
    }



    $lot = ProprietaireLot::where('agence_id', $agenceId)->findOrFail($lotId);
    $montantMaintenances = $this->maintenanceAmountForLot(
        $lotId,
        $data['periode_debut'],
        $data['periode_fin']
    );


    return DB::transaction(function () use ($data, $lotId, $agenceId, $lot, $totalAttendu, $totalEncaisse, $totalLoyerPaye, $totalArrierePaye, $totalRestant, $totalCautionSodeci, $totalFraisDossier, $montantMaintenances) {

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
                'cautionSodeci' => $totalCautionSodeci,
                'depenses_effectuees' => $data['depenses_effectuees'] ?? 0,
                'frais_dossier' => $totalFraisDossier,
                'montant_maintenances' => $montantMaintenances,
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

        $maintenanceIds = Maintenance::query()
            ->where('agence_id', $agenceId)
            ->where('lot_id', $lotId)
            ->where('prise_en_charge_par', Maintenance::PRISE_EN_CHARGE_PROPRIETAIRE)
            ->pluck('maintenance_id');

        TransactionAgence::where('agence_id', $agenceId)
            ->where('type_transaction', TransactionAgence::STATUT_MAINTENANCE)
            ->where('is_reversement', 0)
            ->whereIn('reference', $maintenanceIds)
            ->whereBetween('date_transaction', [$data['periode_debut'].' 00:00:00', $data['periode_fin'].' 23:59:59'])
            ->update([
                'is_reversement' => 1,
                'date_reversement' => now(),
                'reversement_by' => $this->usersId(),
            ]);

       // $lot->statut = 'reverse';
       // $lot->save();

        return redirect()->back()->with('success', 'Reversement effectué avec succès.');
    });
}

public function marquerVenteReverse(Request $request, string $venteId)
{
    $data = $request->validate([
        'periode_debut' => ['required', 'date'],
        'periode_fin' => ['required', 'date', 'after_or_equal:periode_debut'],
    ]);
    $agenceId = $this->agenceId();

    return DB::transaction(function () use ($venteId, $data, $agenceId) {
        $vente = VenteBien::query()
            ->where('agence_id', $agenceId)
            ->with(['lot', 'acheteur'])
            ->lockForUpdate()
            ->findOrFail($venteId);

        if (! $vente->lot_id) {
            abort(422, 'Cette fiche est réservée aux ventes portant sur un lot.');
        }

        $transactions = TransactionAgence::query()
            ->where('agence_id', $agenceId)
            ->where('type_transaction', TransactionAgence::STATUT_VENTE)
            ->where('reference', $vente->getKey())
            ->where('is_reversement', 0)
            ->whereBetween('date_transaction', [$data['periode_debut'].' 00:00:00', $data['periode_fin'].' 23:59:59'])
            ->lockForUpdate()
            ->get();

        $montantEncaisse = (float) $transactions->sum('montant_global_verser');
        if ($montantEncaisse <= 0) {
            abort(422, 'Aucun versement de vente non reversé n’existe sur cette période.');
        }

        $tauxCommission = (float) $vente->prix_vente > 0 && (float) $vente->commission > 0
            ? ((float) $vente->commission / (float) $vente->prix_vente)
            : ((float) (getparametrageAgence($agenceId)->commission ?? 0) / 100);
        $montantCommission = round($montantEncaisse * $tauxCommission, 2);
        $net = $montantEncaisse - $montantCommission;
        $totalVerse = (float) TransactionAgence::query()
            ->where('agence_id', $agenceId)
            ->where('type_transaction', TransactionAgence::STATUT_VENTE)
            ->where('reference', $vente->getKey())
            ->sum('montant_global_verser');

        $reversement = Reversement::create([
            'lot_id' => $vente->lot_id,
            'vente_id' => $vente->getKey(),
            'type_reversement' => 'vente',
            'proprietaire_id' => $vente->proprietaire_id,
            'agence_id' => $agenceId,
            'periode_debut' => $data['periode_debut'],
            'periode_fin' => $data['periode_fin'],
            'total_attendu' => $vente->prix_vente,
            'total_encaisse' => $montantEncaisse,
            'total_restant' => max((float) $vente->prix_vente - $totalVerse, 0),
            'total_loyer_paye' => 0,
            'total_arriere_paye' => 0,
            'taux_commission' => $tauxCommission * 100,
            'montant_commission' => $montantCommission,
            'montant_apres_commission' => $net,
            'nouvelle_caution' => 0,
            'cautionSodeci' => 0,
            'depenses_effectuees' => 0,
            'montant_maintenances' => 0,
            'net_a_reverser' => $net,
            'statut' => 'reverse',
            'date_reversement' => now(),
            'observation' => 'Reversement des versements de la vente '.$vente->reference,
        ]);

        TransactionAgence::whereIn('transaction_agence_id', $transactions->pluck('transaction_agence_id'))
            ->update([
                'is_reversement' => 1,
                'date_reversement' => now(),
                'reversement_by' => $this->usersId(),
            ]);

        return redirect()->route('agence.reversements.historique.detail', $reversement->id_reversement)
            ->with('success', 'Reversement de la vente effectué avec succès : '.number_format($net, 0, ',', ' ').' FCFA reversés au propriétaire.');
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

    $proprietaires = $this->proprietaireService->getPaginated($agenceId, 1000)
        ->getCollection()
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
        $reversement = Reversement::where('agence_id', $this->agenceId())
            ->findOrFail($id);

        return $this->pdfService->download($reversement);
    }


 /**
     * Construit les données d'un reversement archivé, communes
     * à l'affichage Inertia et au PDF.
     */
    protected function buildCourArchive(string $id,?string $debut = null,?string $fin = null): array
    {
        $agenceId = $this->agenceId();

        $reversement = Reversement::with(['proprietaire', 'lot', 'vente.acheteur', 'details.locataire', 'details.porte'])
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

        $venteData = null;
        if ($reversement->type_reversement === 'vente' && $reversement->vente) {
            $vente = $reversement->vente;
            $versements = TransactionAgence::query()
                ->where('agence_id', $agenceId)
                ->where('type_transaction', TransactionAgence::STATUT_VENTE)
                ->where('reference', $vente->getKey())
                ->whereBetween('date_transaction', [optional($reversement->periode_debut)->startOfDay(), optional($reversement->periode_fin)->endOfDay()])
                ->orderBy('date_transaction')
                ->get();
            $totalVerse = (float) TransactionAgence::query()->where('agence_id', $agenceId)
                ->where('type_transaction', TransactionAgence::STATUT_VENTE)->where('reference', $vente->getKey())
                ->sum('montant_global_verser');
            $cumulVerse = (float) TransactionAgence::query()
                ->where('agence_id', $agenceId)
                ->where('type_transaction', TransactionAgence::STATUT_VENTE)
                ->where('reference', $vente->getKey())
                ->where('date_transaction', '<', optional($reversement->periode_debut)->startOfDay())
                ->sum('montant_global_verser');
            $tauxAgence = (float) $reversement->taux_commission;
            $versementsData = $versements->map(function ($transaction) use (&$cumulVerse, $vente, $tauxAgence) {
                $montant = (float) $transaction->montant_global_verser;
                $cumulVerse += $montant;
                $commission = $montant * ($tauxAgence / 100);
                return [
                    'id' => (string) $transaction->getKey(),
                    'date' => optional($transaction->date_transaction)->format('Y-m-d H:i:s'),
                    'montant' => $montant,
                    'tauxAgence' => $tauxAgence,
                    'commissionAgence' => $commission,
                    'netApresCommission' => $montant - $commission,
                    'resteApresVersement' => max((float) $vente->prix_vente - $cumulVerse, 0),
                    'numeroRecu' => $transaction->numero_recu,
                ];
            });
            $venteData = [
                'id' => (string) $vente->getKey(), 'reference' => $vente->reference,
                'dateAccord' => optional($vente->date_accord)->format('Y-m-d'), 'prixVente' => (float) $vente->prix_vente,
                'montantVersePeriode' => (float) $reversement->total_encaisse, 'totalVerse' => $totalVerse,
                'montantRestant' => (float) $reversement->total_restant, 'typePaiement' => $vente->type_paiement,
                'tauxAgence' => (float) $reversement->taux_commission,
                'commissionAgencePeriode' => (float) $reversement->montant_commission,
                'netProprietairePeriode' => (float) $reversement->net_a_reverser,
                'acheteur' => ['nom' => $vente->acheteur?->name ?? '—', 'telephone' => $vente->acheteur?->telephone1 ?? '', 'email' => $vente->acheteur?->email ?? ''],
                'versements' => $versementsData->values()->all(),
            ];
        }

        return [
            'id' => $reversement->id_reversement,
            'nom' => $reversement->lot->name ?? 'Lot',
            'ficheType' => $venteData ? 'vente' : 'location',
            'vente' => $venteData,
            'lot' => ['nom' => $reversement->lot->name ?? 'Lot', 'numero' => $reversement->lot->num_lot ?? '', 'ilot' => $reversement->lot->num_ilot ?? '', 'adresse' => $reversement->lot->adresse ?? ''],
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
            'fraisDossier' => (float) $reversement->frais_dossier,
            'montantMaintenances' => (float) $reversement->montant_maintenances,
            'nouvelleCaution' => (float) $reversement->nouvelle_caution,
            'cautionSodeci' => (float) $reversement->cautionSodeci,
            'observation' => $reversement->observation,
            'locataires' => $locataires,
            'name_entreprise' => getInfoAgence($agenceId)->name,
            'logo_entreprise' => $this->documentBranding->logoUrl(getInfoAgence($agenceId)),
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
