<?php

namespace App\Services;

use App\Models\Agence;
use App\Models\User;
use App\Models\AbonnementHistorique;
use App\Repositories\Interfaces\AgenceRepositoryInterface;
use App\Repositories\Interfaces\TransactionRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Repositories\Interfaces\AbonnementRepositoryInterface;
use App\Events\AgenceCreated;
use App\Events\AgenceUpdated;
use App\Events\AgenceDeleted;
use App\Events\AgenceStatutChange;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Exception;

class AgenceService
{
    public function __construct(
        protected AgenceRepositoryInterface $repository,
        protected TransactionRepositoryInterface $transactionRepository,
        protected UserRepositoryInterface $userRepository,
        protected AbonnementRepositoryInterface $abonnementRepository,
        protected NotificationService $notificationService,
    ) {}

    // ─────────────────────────────────────────────────────────────────────────
    //  CRÉATION
    // ─────────────────────────────────────────────────────────────────────────

    public function createAgence(array $data): Agence
    {
        try {
            DB::beginTransaction();

            $this->validateBeforeCreate($data);

            // 1. Résolution du responsable via le repository
            $responsableId = $this->resolveResponsable($data);
            $data['responsable_id'] = $responsableId;

            // 2. Générer le code agence si non fourni
            if (empty($data['code_agence']) ) {
                $data['code_agence'] = $this->generateCodeAgence();
            }

            // 3. Nettoyage des champs non-colonne avant création
            $agenceData = $this->extractAgenceData($data);

           $agenceDataImge = $this->extractAgenceDataImge($agenceData);

            // 4. Création de l'agence via repository
            $agence = $this->repository->create($agenceDataImge);

           // dd($data['code_agence'],$agenceData,$responsableId,$agence);
            // 5. Gestion de l'abonnement selon le statut
            if ($data['statut'] === 'active') {
                $this->handleAbonnementActif($agence, $data);
            }
           // dd($agenceDataImge,$data,$agence);
         //   dd($data['code_agence'],$agenceData,$responsableId,$agence);
            // 6. Événement

            $updateUser = $this->userRepository->update($responsableId,['agence_id' => $agence['agence_id']]);
         //   dd($agence['agence_id']);
            DB::commit();



            Log::info('Agence créée', [
                'agence_id'   => $agence->agence_id,
                'code_agence' => $agence->code_agence,
                'statut'      => $agence->statut,
            ]);

            return $agence->fresh(['region', 'ville', 'responsable', 'abonnement']);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erreur création agence: ' . $e->getMessage(), [
                'data'  => $data,
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  MISE À JOUR
    // ─────────────────────────────────────────────────────────────────────────

    public function updateAgence(string $id, array $data): Agence
    {
        try {
            DB::beginTransaction();

            $agence = $this->repository->findById($id);

            if (!$agence) {
                throw new Exception("Agence introuvable: {$id}");
            }

            $oldStatut       = $agence->statut;
            $oldAbonnementId = $agence->abonnement_id;

            // Gestion changement de statut
            if (isset($data['statut']) && $data['statut'] !== $oldStatut) {
                $this->handleStatutChange($agence, $data['statut']);
            }

            // Gestion abonnement si passage en active
            if (($data['statut'] ?? $oldStatut) === 'active') {
                $this->handleAbonnementActif($agence, $data, isUpdate: true);
            }

            // Mise à jour responsable si changé
            if (isset($data['responsable_mode'])) {
                $data['responsable_id'] = $this->resolveResponsable($data);
            }

            $agenceData               = $this->extractAgenceData($data);
            $agenceData['updated_by'] = getInfoAdmin()->admin->id_admin ?? 1;

            $agence = $this->repository->update($id, $agenceData);

            event(new AgenceUpdated($agence, $oldStatut, $oldAbonnementId));

            DB::commit();

            return $agence->fresh(['region', 'ville', 'responsable', 'abonnement']);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erreur update agence: ' . $e->getMessage());
            throw $e;
        }
    }

    public function extractAgenceDataImge(array $data){
        if(isset($data['logo']) && !empty($data['logo'])){
            $data['logo'] = upload("agences", 'png', 'logo', $data);
        }
        if(isset($data['signature_responsable']) && !empty($data['signature_responsable'])){
            $data['signature_responsable'] = upload("agences", 'png', 'signature_responsable', $data);
        }
        if(isset($data['signature_comptabilite']) && !empty($data['signature_comptabilite'])){
            $data['signature_comptabilite'] = upload("agences", 'png', 'signature_comptabilite', $data);
        }
        if(isset($data['signature_marketing']) && !empty($data['signature_marketing'])){
            $data['signature_marketing'] = upload("agences", 'png', 'signature_marketing', $data);
        }
        return $data;
    }

    public function getAll(array $filters = [], int $perPage = 15)
    {
        return $this->repository->getAll($filters, $perPage);
    }

    public function findByCode($codeAgence)
    {
        return $this->repository->findByCode($codeAgence);
    }

    public function findByIdOrCode($value)
    {
        return $this->repository->findByIdOrCode($value);
    }

    public function findWithRelations($codeAgence)
    {
        return $this->repository->findWithRelations($codeAgence);
    }


    // ─────────────────────────────────────────────────────────────────────────
    //  SUPPRESSION
    // ─────────────────────────────────────────────────────────────────────────

    public function deleteAgence(string $id): bool
    {
        try {
            $agence = $this->repository->findById($id);

            if (!$agence) {
                throw new Exception("Agence introuvable: {$id}");
            }

            $this->canDeleteAgence($agence);

            $agence->deleted_by = getInfoAdmin()->admin->id_admin ?? 1;
            $agence->save();

            $result = $this->repository->delete($id);

            if ($result) {
                event(new AgenceDeleted($agence));
            }

            return $result;

        } catch (Exception $e) {
            Log::error('Erreur suppression agence: ' . $e->getMessage());
            throw $e;
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  ABONNEMENT
    // ─────────────────────────────────────────────────────────────────────────

    protected function handleAbonnementActif(Agence $agence, array $data, bool $isUpdate = false): void
    {
        $this->validateAbonnementActif($data);

        $dateDebut = Carbon::parse($data['abonnement_start']);
        $dateFin   = Carbon::parse($data['abonnement_end']);
        $dureeMois = (int) ($data['duree_mois'] ?? $dateDebut->diffInMonths($dateFin));

        // Récupérer l'abonnement par défaut si non spécifié
        $abonnementId = $data['abonnement_id'] ?? null;
        if (!$abonnementId) {
            $defaultAbonnement = $this->abonnementRepository->findDefault();
            $abonnementId = $defaultAbonnement?->id;
        }

        $montantBaseHt    = (float) ($data['montant_base_total'] ?? 0);
        $montantOptionsHt = (float) ($data['montant_total'] ?? 0) - $montantBaseHt;
        $montantTotalHt   = (float) ($data['montant_total'] ?? 0);

        // 1. Mise à jour des dates d'abonnement sur l'agence
        $agence->update([
            'abonnement_id'    => $abonnementId,
            'abonnement_start' => $dateDebut,
            'abonnement_end'   => $dateFin,
            'duree_mois'       => $dureeMois,
        ]);

        // 2. Historique d'abonnement
        $historique = AbonnementHistorique::create([
            'agence_id'            => $agence->agence_id,
            'ancien_abonnement_id' => $isUpdate ? $agence->getOriginal('abonnement_id') : null,
            'nouvel_abonnement_id' => $abonnementId,
            'ancienne_date_debut'  => $isUpdate ? $agence->getOriginal('abonnement_start') : null,
            'ancienne_date_fin'    => $isUpdate ? $agence->getOriginal('abonnement_end') : null,
            'nouvelle_date_debut'  => $dateDebut,
            'nouvelle_date_fin'    => $dateFin,
            'duree_mois'           => $dureeMois,
            'montant_ht'           => $montantTotalHt,
            'action'               => $isUpdate ? 'renouvellement' : 'creation',
            'action_par'           => getInfoAdmin()->admin->id_admin ?? 1,
            'notes'                => $data['abonnement_notes'] ?? null,
        ]);

        // 3. Transaction financière
        $this->transactionRepository->create([
            'agence_id'                => $agence->agence_id,
            'abonnement_id'            => $abonnementId,
            'abonnement_historique_id' => $historique->id,
            'montant_base_ht'          => $montantBaseHt,
            'montant_options_ht'       => max(0, $montantOptionsHt),
            'montant_total_ht'         => $montantTotalHt,
            'taux_tva'                 => 0,
            'montant_tva'              => 0,
            'montant_ttc'              => $montantTotalHt,
            'duree_mois'               => $dureeMois,
            'periode_debut'            => $dateDebut,
            'periode_fin'              => $dateFin,
            'options_souscrites'       => $data['options'] ?? [],
            'type_operation'           => $isUpdate ? 'renouvellement' : 'souscription',
            'statut'                   => 'en_attente',
            'created_by'               => getInfoAdmin()->admin->id_admin ?? 1,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  RESPONSABLE — UTILISE LE REPOSITORY
    // ─────────────────────────────────────────────────────────────────────────

    protected function resolveResponsable(array $data): ?string
    {
        $mode = $data['responsable_mode'] ?? 'existing';

        if ($mode === 'existing') {
            return !empty($data['responsable_id']) ? (string) $data['responsable_id'] : null;
        }

        return $this->createResponsable($data);
    }

    protected function createResponsable(array $data): string
    {
        if (empty($data['new_responsable_name']) || empty($data['new_responsable_email'])) {
            throw new Exception("Le nom et l'email du responsable sont obligatoires.");
        }

        // Vérifier via le repository si l'email existe déjà
        if ($this->userRepository->emailExists($data['new_responsable_email'])) {
            throw new Exception("L'email {$data['new_responsable_email']} est déjà utilisé.");
        }

        // Créer via le repository (pas directement User::create)
        $user = $this->userRepository->create([
            'name'           => $data['new_responsable_name'],
            'email'          => $data['new_responsable_email'],
            'password'       => Hash::make($data['new_responsable_password']),
            'tel1'           => $data['new_responsable_tel1'] ?? null,
            'tel2'           => $data['new_responsable_tel2'] ?? null,
            'adresse'        => $data['new_responsable_adresse'] ?? null,
            'photo'          => upload("users_photo", 'png', 'new_responsable_photo', $data),
            'statut'         => true,
            'is_responsable' => true,
            'role_id'        =>1,
            'created_by'     => getInfoAdmin()->admin->id_admin ?? 1,
        ]);

        // Assigner le rôle via Spatie (si utilisé)
        if (method_exists($user, 'assignRole')) {
            $user->assignRole('responsable');
        }

        Log::info('Nouveau responsable créé', [
            'user_id' => $user->id_users,
            'email'   => $user->email,
        ]);

        return $user->id_users;
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  CHANGEMENT DE STATUT
    // ─────────────────────────────────────────────────────────────────────────

    public function changerStatut(string $agenceId, string $nouveauStatut, ?string $motif = null): bool
    {
        try {
            $agence = $this->repository->findById($agenceId);

            if (!$agence) {
                throw new Exception("Agence introuvable");
            }

            $ancienStatut = $agence->statut;

            if ($ancienStatut === $nouveauStatut) {
                return true;
            }

            $this->handleStatutChange($agence, $nouveauStatut);

            $result = $this->repository->updateStatut($agenceId, $nouveauStatut);

            if ($result) {
                DB::table('agence_statut_logs')->insert([
                    'agence_id'      => $agenceId,
                    'ancien_statut'  => $ancienStatut,
                    'nouveau_statut' => $nouveauStatut,
                    'motif'          => $motif,
                    'changed_by'     => getInfoAdmin()->admin->id_admin ?? 1,
                    'created_at'     => now(),
                ]);

                event(new AgenceStatutChange($agence, $ancienStatut, $nouveauStatut, $motif));
            }

            return $result;

        } catch (Exception $e) {
            Log::error('Erreur changement statut: ' . $e->getMessage());
            throw $e;
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  HELPERS PROTÉGÉS
    // ─────────────────────────────────────────────────────────────────────────

    protected function extractAgenceData(array $data): array
    {
        $columns = [
            'agence_id', 'code_agence', 'name', 'adresse',
            'tel1', 'tel2', 'email1', 'email2',
            'region_id', 'ville_id',
            'statut', 'is_principale', 'parent_id',
            'responsable_id',
            'abonnement_id', 'abonnement_start', 'abonnement_end', 'duree_mois',
            'logo', 'signature_responsable', 'signature_comptabilite', 'signature_marketing',
            'created_by', 'updated_by', 'deleted_by',
        ];

        $agenceData = array_intersect_key($data, array_flip($columns));

        // Mapper 'region' → 'region_id'
        if (!isset($agenceData['region_id']) && isset($data['region'])) {
            $agenceData['region_id'] = $data['region'];
        }

        if (!isset($agenceData['created_by'])) {
            $agenceData['created_by'] = getInfoAdmin()->admin->id_admin ?? 1;
        }

        return $agenceData;
    }

    protected function validateBeforeCreate(array $data): void
    {
        if (!empty($data['email1'])) {
            if (Agence::where('email1', $data['email1'])->exists()) {
                throw new Exception("L'email '{$data['email1']}' est déjà utilisé par une autre agence.");
            }
        }

        if (!empty($data['tel1'])) {
            if (Agence::where('tel1', $data['tel1'])->exists()) {
                throw new Exception("Le téléphone '{$data['tel1']}' est déjà utilisé par une autre agence.");
            }
        }
    }

    protected function validateAbonnementActif(array $data): void
    {
        if (empty($data['abonnement_end'])) {
            throw new Exception("La date de fin d'abonnement est obligatoire pour une agence active.");
        }

        $dateFin = Carbon::parse($data['abonnement_end']);

        if ($dateFin->isPast()) {
            throw new Exception("La date de fin d'abonnement ne peut pas être dans le passé.");
        }

        if (!empty($data['abonnement_start'])) {
            $dateDebut = Carbon::parse($data['abonnement_start']);
            if ($dateFin->lessThanOrEqualTo($dateDebut)) {
                throw new Exception("La date de fin doit être postérieure à la date de début.");
            }
        }
    }

    protected function handleStatutChange(Agence $agence, string $nouveauStatut): void
    {
        if ($nouveauStatut === 'active') {
            if ($agence->abonnement_end && Carbon::parse($agence->abonnement_end)->isPast()) {
                throw new Exception("Impossible d'activer l'agence: abonnement expiré.");
            }
        }

        if ($nouveauStatut === 'desactive' && $agence->is_principale) {
            throw new Exception("L'agence principale d'une région ne peut pas être désactivée.");
        }
    }

    protected function canDeleteAgence(Agence $agence): void
    {
        $hasProprietes = DB::table('proprietes')->where('agence_id', $agence->agence_id)->exists();
        $hasContrats   = DB::table('contrats')->where('agence_id', $agence->agence_id)->exists();
        $hasLocataires = DB::table('locataires')->where('agence_id', $agence->agence_id)->exists();

        if ($hasProprietes || $hasContrats || $hasLocataires) {
            throw new Exception("Cette agence ne peut pas être supprimée: elle possède des données associées.");
        }

        if ($agence->is_principale) {
            throw new Exception("L'agence principale d'une région ne peut pas être supprimée.");
        }
    }

    protected function generateCodeAgence(): string
    {
        $year = now()->year;
        $last = Agence::whereYear('created_at', $year)->count() + 1;
        return sprintf("AG-%d-%04d", $year, $last);
    }
}
