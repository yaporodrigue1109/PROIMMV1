<?php

namespace App\Repositories\Agence\Repository;

use App\Models\Locataire;
use App\Models\LocataireAgence;
use App\Models\Loyer;
use App\Models\Porte;
use App\Models\TransactionAgence;
use App\Repositories\Agence\Interfaces\LocataireRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Repositories\Agence\Interfaces\ParametrageAgenceRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class LocataireRepository implements LocataireRepositoryInterface
{
    protected  $parametrageRepository;
    // =========================================================================
    // LECTURE
    // =========================================================================

    public function __construct(ParametrageAgenceRepositoryInterface $parametrageRepository)
    {
        $this->parametrageRepository = $parametrageRepository;

    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $agenceId = $this->agenceId();

        // ── Callback eager-load synchronisé avec les filtres actifs ──────────
        // Un locataire peut avoir N contrats actifs dans la même agence
        // (ex : 2 portes différentes). On charge TOUS ses contrats correspondants,
        // pas seulement le premier.
        $contratsEagerLoad = function ($q) use ($agenceId, $filters) {
            $q->where('agence_id', $agenceId)
                ->with(['porte.tarifActif', 'propriete', 'proprietaire', 'batiment', 'lot']);

            // Synchroniser : si on filtre les locataires par statut actif,
            // on ne charge que leurs contrats actifs (évite d'afficher des
            // contrats résiliés d'autres portes dans la vue)
            if (!empty($filters['is_actif'])) {
                $q->where('is_active', true);
            }

            // Synchroniser : si on filtre par propriété, on ne charge que
            // les contrats de cette propriété (un locataire multi-propriétés
            // n'affiche que ce qui est pertinent au contexte)
            if (!empty($filters['propriete_id'])) {
                $q->where('propriete_id', $filters['propriete_id']);
            }
        };

        // ── Requête principale ────────────────────────────────────────────────
        $query = Locataire::with(['contrats' => $contratsEagerLoad])
            ->whereHas('contrats', fn($q) => $q->where('agence_id', $agenceId));

        // ── Filtres ───────────────────────────────────────────────────────────
        if (!empty($filters['search'])) {
            $query->search($filters['search']);
        }

        // Au moins un contrat actif dans cette agence
        if (!empty($filters['is_actif'])) {
            $query->whereHas('contrats', fn($q) => $q
                ->where('agence_id', $agenceId)
                ->where('is_active', true)
            );
        }

        // Au moins un contrat sur cette propriété dans cette agence
        // (le filtre whereHas exclut le locataire si aucune de ses portes
        // n'appartient à la propriété demandée)
        if (!empty($filters['propriete_id'])) {
            $query->whereHas('contrats', fn($q) => $q
                ->where('agence_id', $agenceId)
                ->where('propriete_id', $filters['propriete_id'])
            );
        }

        return $query->orderBy('name')->paginate($perPage);
    }

    public function findById(string $id): ?Locataire
    {
        $agenceId = $this->agenceId();
        if (!$agenceId) {
            return null;
        }

        return Locataire::with([
            'region',
            'ville',
            'genre',

            // Contrats de cette agence uniquement + leurs relations
                'contrats' => fn($q) => $q
                ->where('agence_id', $agenceId)
                ->with([
                    'porte.tarifActif',
                    'propriete',
                    'proprietaire',
                    'batiment',
                    'lot',
                    'periodicitePaiement',
                    'modePaiement',
                ]),

            // Loyers de cette agence uniquement
            'loyers' => fn($q) => $q
                ->where('agence_id', $agenceId)
                ->orderByDesc('annee_paiement')
                ->orderByDesc('mois_paiement')
                ->with([
                   'modePaiement'
                ]),

            // Transactions de cette agence uniquement
            'transactions' => fn($q) => $q
                ->where('agence_id', $agenceId)
                ->orderByDesc('date_transaction')
                ->with([
                    'modePaiement'
                ])
                ->limit(10),
        ])
            // S'assurer que le locataire appartient bien à cette agence
            ->whereHas('contrats', fn($q) => $q->where('agence_id', $agenceId))
            ->find($id);
    }

    public function findByCode(string $code): ?Locataire
    {
        return Locataire::where('code', $code)->first();
    }

    public function findByPieceOrTel(?string $numPiece = null, ?string $tel = null): ?Locataire
    {
        return Locataire::where(function ($q) use ($numPiece, $tel) {
            if ($numPiece) {
                $q->orWhere('num_piece', $numPiece);
            }
            if ($tel) {
                $q->orWhere('tel1', $tel)->orWhere('tel2', $tel);
            }
        })->first();
    }

    public function existeContratSurMemPorte(
        Locataire $locataire,
        string $porteId,
        string $proprietaireId
    ): bool {
        return LocataireAgence::where('agence_id', $this->agenceId())
            ->where('locataire_id', $locataire->locataire_id)
            ->where('porte_id', $porteId)
            ->where('proprietaire_id', $proprietaireId)
            ->where('is_active', true)
            ->exists();
    }

    public function stats(): array
    {
        $agenceId = $this->agenceId();
        $base     = LocataireAgence::where('agence_id', $agenceId);

        return [
            'total'    => (clone $base)->distinct('locataire_id')->count(),
            'actifs'   => (clone $base)->where('is_active', true)->count(),
            'resilies' => (clone $base)->where('is_active', false)->count(),
            'ce_mois'  => (clone $base)
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
        ];
    }

    // =========================================================================
    // ÉCRITURE LOCATAIRE
    // =========================================================================

    public function create(array $data): Locataire
    {

        try {
            // $data['locataire_id'] = $data['locataire_id'] ?? (string) Str::uuid();
            $data['code']         = $data['code']         ?? $this->generateCode();
            $response =Locataire::create($data);

            return $response;
        }catch (\Exception $exception){
         //   dd($exception->getMessage());
        }

    }

    public function update(Locataire $locataire, array $data): Locataire
    {
        $locataire->update($data);
        return $locataire->fresh();
    }

    public function delete(Locataire $locataire): bool
    {
        return $locataire->delete();
    }

    // =========================================================================
    // ENREGISTREMENT COMPLET (RÈGLES MÉTIER)
    // =========================================================================

    /**
     * Point d'entrée principal.
     *
     * $paiementData attendu :
     * {
     *   mois_avance      : int,          // nombre de mois d'avance payés
     *   montant_loyer    : float,        // montant mensuel du loyer
     *   montant_agence   : float,        // commission agence mensuelle
     *   montant_proprio  : float,        // part propriétaire mensuelle
     *   montant_total    : float,        // total encaissé
     *   date_debut       : string,       // Y-m-d, premier mois à facturer
     *   arrieres         : array,        // [{mois, annee, montant_a_payer, montant_payer, ...}]
     * }
     */
    public function enregistrer(array $locataireData, array $contratData, array $paiementData): array
    {

        try {


            return DB::transaction(function () use ($locataireData, $contratData, $paiementData) {

                // ── 1. Récupérer ou créer le locataire ────────────────────────────
                $locataire = $this->findByPieceOrTel(
                    $locataireData['num_piece'] ?? null,
                    $locataireData['tel1']      ?? null
                );

                $isNew = filter_var($locataireData['is_new'] ?? true, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE);
                $isNew = $isNew ?? true;
                $locataireData['is_new'] = $isNew;


                $existeLocataire = is_null($locataire);

                if ($existeLocataire) {

                    $locataire = $this->create($locataireData);

                }



                // ── 2. Vérifier doublon (même porte + même propriétaire) ──────────
                if ($this->existeContratSurMemPorte(
                    $locataire,
                    $contratData['porte_id'],
                    $contratData['proprietaire_id']
                )) {
                    throw new \RuntimeException(
                        "Ce locataire habite déjà chez ce propriétaire dans cette maison."
                    );
                }


                // ── 3. Créer le contrat ────────────────────────────────────────────
                $contrat = $this->createContrat($locataire, $contratData, $isNew);

                // ── 4 / 5 / 6. Loyers & transactions selon le cas ─────────────────
                $arrieres = $paiementData ?? [];



                // si $estAncien==0 donc c'est un ancien locataire sinon c'est un nouveau locataire
                if ($isNew) {

                    // Règle 4 : nouveau locataire → factures avance + 1 transaction
               //   if (($paiementData['mois_avance'] ?? 0) > 0) {
                      $this->genererFacturesAvance($locataire, $contrat, $contratData);
                   // }



                } else {
                    if (empty($arrieres)) {
                        // Règle 5 : ancien locataire sans arriéré → rien
                    } else {

                        // Règle 6 : ancien locataire avec arriéré → loyers seulement
                        $this->enregistrerArrieres($locataire, $contrat, $arrieres);
                    }
                }

                return [
                    'locataire'  => $locataire->fresh(),
                    'contrat'    => $contrat->fresh(),
                    'estNouveau' => $existeLocataire,
                ];
            });





        }catch (\Exception $exception){
            dd($exception->getMessage());
        }


    }

    // =========================================================================
    // CONTRAT
    // =========================================================================

    public function createContrat(Locataire $locataire, array $data, bool $isNew = true): LocataireAgence
    {

        try {

            // Désactiver les contrats actifs sur la même porte
            LocataireAgence::where('porte_id', $data['porte_id'])
                ->where('agence_id', $this->agenceId())
                ->where('is_active', true)
                ->update(['is_active' => false]);
            $porte =    Porte::with('batiment.propriete', 'tarifActif')->find($data['porte_id']);

            // Marquer la porte comme occupée
         $porte->update(['is_occupe' => true]);
       //     dd($data,$locataire,$porte->batiment?->propriete?->lot_id);
            $tarifActif = $porte?->tarifActif;
            $pasDePorte = (float) ($tarifActif?->mt_autre_frais ?? 0);
            $loyerNet = (float) ($porte?->mt_loyer ?? $tarifActif?->mt_loyer ?? 0);
            $caution = (float) ($porte?->caution ?? $tarifActif?->mt_caution ?? 0);
            $avance = (float) ($porte?->avance ?? $tarifActif?->mt_avance ?? 0);
            $agence = (float) ($porte?->agence ?? $tarifActif?->mt_frais_agence ?? 0);
            $cautionCie = (float) ($porte?->mt_caution_cie ?? $tarifActif?->mt_caution_cie ?? 0);
            $cautionSodeci = (float) ($porte?->mt_caution_sodeci ?? $tarifActif?->mt_caution_sodeci ?? 0);
            $fraisDeDossier = (float) ($data['frais_de_dossier'] ?? $data['frais_annexe'] ?? $tarifActif?->mt_frais_dossier ?? 0);
            $montantGlobalGarantie = ($loyerNet * ($caution + $avance + $agence))
                + $cautionCie
                + $cautionSodeci
                + $fraisDeDossier
                + $pasDePorte;

            return LocataireAgence::create([
                ...$data,
                'loyer_net' => $loyerNet,
                'caution' => $caution,
                'avance' => $avance,
                'agence' => $agence,
                'caution_cie' => $cautionCie,
                'caution_sodeci' => $cautionSodeci,
                'frais_de_dossier' => $fraisDeDossier,
                'pas_de_porte' => $pasDePorte,
                'montant_global_garantie' => $montantGlobalGarantie,
             'lot_id' => $porte->batiment?->propriete?->lot_id,
                'locataire_id'        => $locataire->locataire_id,
                'agence_id'           => $this->agenceId(),
                'is_active'           => true,
                'is_new'              => $isNew,
            ]);

        }catch (\Exception $exception){
            dd($exception->getMessage());
        }



    }

    public function resilierContrat(Locataire $locataire): bool
    {
        $contrat = $locataire->contrats()
            ->where('agence_id', $this->agenceId())
            ->where('is_active', true)
            ->first();

        if (!$contrat) {
            return false;
        }

        $contrat->update(['is_active' => false]);

        // Libérer la porte
        \App\Models\Porte::find($contrat->porte_id)?->update(['is_occupe' => false]);

        return true;
    }

    // =========================================================================
    // LOYERS & TRANSACTIONS
    // =========================================================================

    /**
     * Règle 4 : Nouveau locataire avec avances.
     * → Une facture Loyer par mois d'avance.
     * → Une seule TransactionAgence récapitulative.
     */
    public function genererFacturesAvance(
        Locataire       $locataire,
        LocataireAgence $contrat,
        array           $paiementData
    ): void {

        try {
            $dateDebut      = Carbon::parse($paiementData['date_entree']);
            $porte = Porte::find($paiementData['porte_id']);
            $parametrage = $this->parametrageRepository->getByAgence($contrat->agence_id);
            $moisAvance     = (int)   $porte->avance;
            $moisCaution     = (int)   $porte->caution;
            $moisAgence     = (int)   $porte->agence;
            $mt_loyer     = (int)   $porte->mt_loyer;
            $mt_caution_cie = (int)   $porte->mt_caution_cie;
            $mt_caution_sodeci = (int)   $porte->mt_caution_sodeci;
            $mt_autre_frais     = (int)   $porte->mt_autre_frais;
            $montantTotal = $mt_loyer* ($moisAvance + $moisCaution + $moisAgence) + $mt_caution_cie + $mt_caution_sodeci + $mt_autre_frais;

            $percent = $parametrage->commission / 100;

            $montant_agence = $mt_loyer *$percent;
            $montant_propio = $mt_loyer - $montant_agence;

            $moisPayer =[];

            // ── Factures loyer (une par mois) ─────────────────────────────────────
            for ($i = 0; $i < $moisAvance; $i++) {
                $dateMois = $dateDebut->copy()->addMonths($i);

                $dateLimite = clone $dateMois;
                $dateLimite->day(10)->setTime(23, 59, 59);

                // Extraction correcte
                $mois = (int) $dateMois->format('m');
                $annee = (int) $dateMois->format('Y');
                $moisPayer[] = formatMoisAnnee($mois, $annee);

                Loyer::create([

                    'locataire_id'          => $locataire->locataire_id,
                    'proprietaire_id'       => $contrat->proprietaire_id,
                    'agence_id'             => $contrat->agence_id,
                    'propriete_id'          => $contrat->propriete_id,
                    'batiment_id'           => $contrat->batiment_id,
                    'porte_id'              => $contrat->porte_id,
                    'lot_id'                => $contrat->lot_id,
                    'statut'                => Loyer::STATUT_PAYE,
                    'montant_a_payer'       => $mt_loyer,
                    'montant_payer'         => $mt_loyer,
                    'montant_restant'       => 0,
                    'montant_agence'        => $montant_agence,
                    'montant_proprio'        => $montant_propio,
                    'montant_global_proprio'=> $montant_propio,
                    'montant_global_agence' => $montant_agence,
                    'mode_paiement_id' =>       $paiementData['mode_paiement_id'],
                    'arriere_precedent'     => 0,
                    'is_first'              => $i === 0,
                    'mois_paiement'         => $mois,
                    'annee_paiement'        => $annee,
                    'date_paiement'         => now(),
                    'date_limit_paiement'   => $dateLimite,
                    'creaeted_by'           => $this->userId(),
                ]);
            }


//
//        // ── Transaction récapitulative unique ─────────────────────────────────
            TransactionAgence::create([

                'locataire_id'          => $locataire->locataire_id,
                'agence_id'             => $contrat->agence_id,
                'proprietaire_id'       => $contrat->proprietaire_id,
                'propriete_id'          => $contrat->propriete_id,
                'batiment_id'           => $contrat->batiment_id,
                'porte_id'              => $contrat->porte_id,
                'montant_global_verser' =>(int)  $montantTotal,
                'mois_payer'            => json_encode($moisPayer),
                'arriere_actuel'        => 0,
                'montant_arriere_payer' => 0,
                'montant_loyer_payer'   =>  $mt_loyer,
                'montant_avance_payer'  => (int)  ($mt_loyer *$moisAvance),
                'is_first'              => true,
                'is_reversement'        => false,
                'date_transaction'      => now(),
                'mode_paiement_id' =>       $paiementData['mode_paiement_id'],
                'created_by'            => $this->userId(),
            ]);
        }catch (\Exception $exception){
            dd($exception->getMessage());
        }

    }

    /**
     * Règle 6 : Ancien locataire avec arriérés.
     * → Crée une ligne Loyer par arriéré.
     * → Ne touche PAS transaction_agence.
     *
     * Chaque élément de $arrieres :
     * {
     *   mois             : int,
     *   annee            : int,
     *   montant_a_payer  : float,
     *   montant_payer    : float,   // peut être 0 si impayé
     *   commentaire      : string|null,
     * }
     */
    public function enregistrerArrieres(
        Locataire       $locataire,
        LocataireAgence $contrat,
        array           $arrieres
    ): void {
        try {
            foreach ($arrieres as $arriere) {

                $date = Carbon::createFromFormat('Y-m', $arriere['mois']);
                $annee = $date->year;  // 2026
                $mois = $date->month;

                $dateDebut = Carbon::create(
                    (int) $annee,
                    (int) $mois,
                    1
                )->day(10)->setTime(23, 59, 59);


                Loyer::create([

                    'locataire_id'          => $locataire->locataire_id,
                    'proprietaire_id'       => $contrat->proprietaire_id,
                    'agence_id'             => $contrat->agence_id,
                    'propriete_id'          => $contrat->propriete_id,
                    'batiment_id'           => $contrat->batiment_id,
                    'porte_id'              => $contrat->porte_id,
                    'lot_id'                => $contrat->lot_id,
                    'statut'                => Loyer::STATUT_IMPAYE,
                    'montant_a_payer'       => $arriere['montant'],
                    'montant_payer'         => 0,
                    'montant_restant'       => $arriere['montant'],
                    'montant_proprio'       => 0,
                    'montant_agence'        => 0,
                    'montant_global_proprio'=> 0,
                    'montant_global_agence' => 0,
                    'arriere_precedent'     => 0,
                    'mode_paiement_id' =>       $contrat['mode_paiement_id'] ?? 1,
                    'is_first'              => false,
                    'mois_paiement'         => (int) $mois,
                    'annee_paiement'        => (int) $annee,
                    'date_paiement'         =>  null,
                    'date_limit_paiement'   => $dateDebut,
                    'commentaire'           => null,
                    'creaeted_by'           => $this->userId(),
                ]);
            }
        }catch (\Exception $exception){
            dd($exception->getMessage());
        }

    }

    public function newlocataire(  Locataire $locataire,LocataireAgence $contrat): void
    {
      //  return true;
    }

    // =========================================================================
    // HELPERS PRIVÉS
    // =========================================================================

    public function generateCode(): string
    {
        do {
            $letters = strtoupper(chr(rand(65, 90)) . chr(rand(65, 90)));
            $numbers = str_pad(rand(0, 99999), 5, '0', STR_PAD_LEFT);
            $code    = $letters . '-' . $numbers;
        } while (Locataire::where('code', $code)->exists());

        return $code;
    }

    private function agenceId(): string
    {
        return getInfoAgent()?->users?->agence_id ?? auth('user')->user()?->agence_id ?? '';
    }

    private function userId(): string
    {
        return getInfoAgent()?->users?->id ?? auth('user')->user()?->id ?? 'system';
    }


}
