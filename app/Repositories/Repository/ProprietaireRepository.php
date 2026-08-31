<?php

namespace App\Repositories\Repository;

use App\Repositories\Interfaces\ProprietaireRepositoryInterface;
use App\Models\Proprietaire;
use App\Models\ProprietaireAgence;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use App\Models\LocataireAgence;
use DomainException;
use App\Models\MobileApiToken;

class ProprietaireRepository implements ProprietaireRepositoryInterface
{
    public function getAllByAgence(string $agenceId, int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = Proprietaire::query()
            ->whereHas('agences', fn ($q) => $q->where('agence_id', $agenceId)->where('is_active', true))
            ->with(['agences' => fn ($q) => $q->where('agence_id', $agenceId)->where('is_active', true)])
            ->withCount([
                'lots' => fn ($q) => $q->where('agence_id', $agenceId),
                'proprietes' => fn ($q) => $q->where('agence_id', $agenceId),
            ])
            ->latest('created_at');

        if (!empty($filters['search'])) {
            $search = trim((string) $filters['search']);

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('tel1', 'like', "%{$search}%")
                    ->orWhere('tel2', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('numpiece', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('adresse', 'like', "%{$search}%");
            });
        }

        return $query->paginate($perPage);
    }

    public function findById(string $id): ?Proprietaire
    {
        $agenceId=  getInfoAgent()->users->agence_id ;
        return Proprietaire::with([
                'agences' => fn ($q) => $q->where('agence_id', $agenceId),
                'region',
                'ville',
        ])->find($id);
    }

    public function findByIdAndAgence(string $id, string $agenceId): ?Proprietaire
    {

        return Proprietaire::with([
            'agences' => fn ($q) => $q->where('agence_id', $agenceId),
            'region',
            'ville',
        ])
            ->whereHas('agences', fn ($q) => $q->where('agence_id', $agenceId)->where('is_active', true))
            ->find($id);
    }



    public function create(array $proprietaireData, array $agenceData, string $agenceId): Proprietaire
    {
        return DB::transaction(function () use ($proprietaireData, $agenceData, $agenceId) {

            // ✅ exists() directement sur la query, pas sur le résultat
            $existeDejaEnBase = Proprietaire::where('numpiece', $proprietaireData['numpiece'])
                ->orWhere('tel1', $proprietaireData['tel1'])
                ->exists();

            if ($existeDejaEnBase) {
                $proprietaire = Proprietaire::where('numpiece', $proprietaireData['numpiece'])
                    ->orWhere('tel1', $proprietaireData['tel1'])
                    ->first();
            } else {

                $proprietaire = Proprietaire::create($proprietaireData);
            }

            // Vérifie si le lien agence existe déjà pour éviter les doublons
            $liaisonExiste = ProprietaireAgence::where('proprietaire_id', $proprietaire->proprietaire_id)
                ->where('agence_id', $agenceId)
                ->exists();

            if (!$liaisonExiste) {
                ProprietaireAgence::create(array_merge($agenceData, [
                    'proprietaire_id' => $proprietaire->proprietaire_id,
                    'agence_id'       => $agenceId,
                    'is_active'       => true,
                    'date_activation' => now(),
                ]));
            }

            return $proprietaire->load('agences');
        });
    }

    public function update(string $id, array $proprietaireData, array $agenceData): Proprietaire
    {
        return DB::transaction(function () use ($id, $proprietaireData, $agenceData) {
            $proprietaire = Proprietaire::findOrFail($id);
            //$proprietaireData['updated_by']  = getInfoAgent()->users->id_users ?? getInfoAdmin()->admin->id_admin;
           // dd($proprietaireData);
            $proprietaire->update($proprietaireData);

            $agenceId = getInfoAgent()->users->agence_id ?? null;
            $liaison = $agenceId
                ? $proprietaire->agences()->where('agence_id', $agenceId)->latest('created_at')->first()
                : null;

            if ($liaison) {
                $liaison->update($agenceData);
            } elseif ($agenceId && !empty($agenceData)) {
                ProprietaireAgence::create(array_merge($agenceData, [
                    'proprietaire_id' => $proprietaire->proprietaire_id,
                    'agence_id'       => $agenceId,
                    'is_active'       => true,
                    'date_activation' => now(),
                ]));
            }
           
            return $proprietaire->fresh('agences');
        });
    }

    public function delete(string $id): bool
    {
        $agenceId = getInfoAgent()->users->agence_id ?? null;

        $this->ensureNoActiveTenant($id, $agenceId);

        $link = ProprietaireAgence::where(['proprietaire_id' => $id, 'agence_id' => $agenceId])->first();
        if (!$link) {
            return false;
        }

        $link->forceFill([
            'is_active' => false,
            'is_mobile_visible' => false,
            'date_desactivation' => now(),
            'deleted_by' => getInfoAgent()->users->id_users ?? getInfoAdmin()->admin->id_admin,
        ])->save();

        $deleted = (bool) $link->delete();
        $this->revokeOwnerMobileIfNoActiveLink($id);

        return $deleted;

    }

    public function activate(string $proprietaireAgenceId): bool
    {
        return (bool) ProprietaireAgence::where('proprietaire_agence_id', $proprietaireAgenceId)
            ->where('agence_id', getInfoAgent()->users->agence_id)
            ->update([
                'is_active'           => true,
                'date_activation'     => now(),
                'updated_by'  => getInfoAgent()->users->id_users ?? getInfoAdmin()->admin->id_admin,
                'date_desactivation'  => null,
                'agent_activation_id' => getInfoAgent()->users->id_users ?? getInfoAdmin()->admin->id_admin,
            ]);
    }

    public function deactivate(string $proprietaireAgenceId): bool
    {
        $link = ProprietaireAgence::where('proprietaire_agence_id', $proprietaireAgenceId)
            ->where('agence_id', getInfoAgent()->users->agence_id)
            ->first();
        if (!$link) {
            return false;
        }

        $this->ensureNoActiveTenant($link->proprietaire_id, $link->agence_id);

        $deactivated = (bool) ProprietaireAgence::where('proprietaire_agence_id', $proprietaireAgenceId)
            ->update([
                'is_active'               => false,
                'is_mobile_visible'       => false,
                'date_desactivation'      => now(),
                'updated_by'  => getInfoAgent()->users->id_users ?? getInfoAdmin()->admin->id_admin,
                'agent_desactivation_id'  => getInfoAgent()->users->id_users ?? getInfoAdmin()->admin->id_admin,
            ]);

        $this->revokeOwnerMobileIfNoActiveLink($link->proprietaire_id);

        return $deactivated;
    }

    private function revokeOwnerMobileIfNoActiveLink(string $proprietaireId): void
    {
        $hasAnotherActiveLink = ProprietaireAgence::where('proprietaire_id', $proprietaireId)
            ->where('is_active', true)
            ->exists();

        if (! $hasAnotherActiveLink) {
            MobileApiToken::where('actor_type', 'proprietaire')
                ->where('actor_id', $proprietaireId)
                ->delete();
        }
    }

    private function ensureNoActiveTenant(string $proprietaireId, ?string $agenceId): void
    {
        $hasActiveTenant = LocataireAgence::withoutGlobalScope('active_proprietaire')
            ->where('proprietaire_id', $proprietaireId)
            ->when($agenceId, fn ($query) => $query->where('agence_id', $agenceId))
            ->where('is_active', true)
            ->exists();

        if ($hasActiveTenant) {
            throw new DomainException('Ce propriétaire a au moins un locataire actif. Résiliez d’abord tous les contrats actifs.');
        }
    }


    // ─── À ajouter dans ProprietaireRepository ───────────────────

    /**
     * Retourne tous les propriétaires qui ont au moins une porte
     * libre (is_occupe = false, is_actif = true) dans leurs propriétés.
     *
     * Utile pour : sélecteur de propriétaire lors de la création
     * d'un contrat de location.
     */
    public function getAvecPortesDisponibles(): \Illuminate\Database\Eloquent\Collection
    {
        return Proprietaire::whereHas('proprietes', function ($q) {
            $q->where('is_actif', true)
                ->whereHas('batiments.portes', function ($q2) {
                    $q2->where('is_occupe', false)
                        ->where('is_actif', true);
                });
        })
            ->with([
                'proprietes' => fn($q) => $q
                    ->where('is_actif', true)
                    ->with([
                        'batiments.portes' => fn($q2) => $q2
                            ->where('is_occupe', false)
                            ->where('is_actif', true)
                            ->with(['typePorte', 'tarifActif']),
                    ]),
            ])
            ->orderBy('name')
            ->get();
    }

    /**
     * Version avec filtre par agence (multi-tenant).
     * Retourne les propriétaires dont les propriétés appartiennent
     * à l'agence connectée ET ont des portes disponibles.
     */
    public function getAvecPortesDisponiblesByAgence(?string $agenceId = null): \Illuminate\Database\Eloquent\Collection
    {
        $agenceId = $agenceId ?? $this->agenceId();

        return Proprietaire::whereHas('proprietes', function ($q) use ($agenceId) {
            $q->where('agence_id', $agenceId)
                ->where('is_actif', true)
                ->whereHas('batiments.portes', function ($q2) {
                    $q2->where('is_occupe', false)
                        ->where('is_actif', true);
                });
        })
            ->with([
                'lots' => fn($q) => $q
                    ->where('agence_id', $agenceId)
                    ->with([
                        'region',
                        'ville',
                        'proprietes' => fn($q2) => $q2
                            ->where('agence_id', $agenceId)
                            ->where('is_actif', true)
                            ->with([
                                'batiments.portes' => fn($q3) => $q3
                                    ->where('is_occupe', false)
                                    ->where('is_actif', true)
                                    ->with(['typePorte', 'tarifActif']),
                            ]),
                    ]),
                'proprietes' => fn($q) => $q
                    ->where('agence_id', $agenceId)
                    ->where('is_actif', true)
                    ->with([
                        'batiments.portes' => fn($q2) => $q2
                            ->where('is_occupe', false)
                            ->where('is_actif', true)
                            ->with(['typePorte', 'tarifActif']),
                    ]),
            ])
            ->orderBy('name')
            ->get();
    }

    private function agenceId(): string
    {
        return getInfoAgent()->users->agence_id;
    }
}
