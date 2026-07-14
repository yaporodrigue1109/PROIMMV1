<?php

namespace App\Repositories\Agence\Repository;

use App\Models\Maintenance;
use App\Models\MaintenanceDetail;
use App\Repositories\Agence\Interfaces\MaintenanceRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MaintenanceRepository implements MaintenanceRepositoryInterface
{
    protected  $model;
    public function __construct( Maintenance $model) {
        $this->model = $model;
    }

    // =========================================================================
    // CRUD de base
    // =========================================================================

    public function all(array $filters = [])
    {
        $query = $this->model->query();

        if (!empty($filters['statut'])) {
            $query->where('statut', $filters['statut']);
        }

        if (!empty($filters['maintenancier_id'])) {
            $query->where('maintenancier_id', $filters['maintenancier_id']);
        }

        if (!empty($filters['maintenancier_id'])) {
            $query->whereHas('details', fn ($q) =>
            $q->where('maintenancier_id', $filters['maintenancier_id'])
            );
        }

        if (!empty($filters['agence_id'])) {
            $query->where('agence_id', $filters['agence_id']);
        }

        if (!empty($filters['date_debut']) && !empty($filters['date_fin'])) {
            $query->whereBetween('created_at', [$filters['date_debut'], $filters['date_fin']]);
        }

        return $query->latest()
            ->withDefaultRelations()->paginate($filters['per_page'] ?? 15);
    }

    public function find($id)
    {
        return $this->model->withDefaultRelations()->find($id);
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function update($id, array $data)
    {
        $maintenance = $this->find($id);
        if ($maintenance) {
            $maintenance->update($data);
            return $maintenance;
        }
        return null;
    }

    public function delete($id)
    {
        $maintenance = $this->find($id);
        if ($maintenance) {
            return $maintenance->delete();
        }
        return false;
    }

    public function restore($id)
    {
        return $this->model->withDefaultRelations()->withTrashed()->findOrFail($id)->restore();
    }

    public function forceDelete($id)
    {
        return $this->model->withDefaultRelations()->withTrashed()->findOrFail($id)->forceDelete();
    }

    // =========================================================================
    // Requêtes métier simples
    // =========================================================================

    public function getByStatut($statut)
    {
        return $this->model->withDefaultRelations()->where('statut', $statut)->get();
    }

    public function getByMaintenancier($maintenancierId)
    {
        return $this->model->where('maintenancier_id', $maintenancierId)->get();
    }

    public function getByProprietaire($proprietaireId)
    {
        return $this->model->withDefaultRelations()->where('proprietaire_id', $proprietaireId)->get();
    }

    public function getByAgence($agenceId)
    {
        return $this->model->withDefaultRelations()->where('agence_id', $agenceId)->get();
    }

    public function getByDateRange($startDate, $endDate)
    {
        return $this->model->withDefaultRelations()->whereBetween('created_at', [$startDate, $endDate])->get();
    }

    public function countByStatut()
    {
        return $this->model->withDefaultRelations()
            ->select('statut', DB::raw('count(*) as total'))
            ->groupBy('statut')
            ->pluck('total', 'statut')
            ->toArray();
    }

    public function getMontantTotalParMois($year)
    {
        return $this->model->withDefaultRelations()
            ->whereYear('created_at', $year)
            ->where('statut', 'termine')
            ->select(
                DB::raw('MONTH(created_at) as mois'),
                DB::raw('SUM(montant_global) as total')
            )
            ->groupBy('mois')
            ->pluck('total', 'mois')
            ->toArray();
    }

    // =========================================================================
    // Opérations avancées (avec détails)
    // =========================================================================

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->applyFilters($this->model->newQuery(), $filters)
            ->withDefaultRelations()
            ->latest('created_at')
            ->paginate($perPage);
    }

    public function findById(string $id, bool $withDetails = true): ?Maintenance
    {
        $query = $this->model->newQuery();

        if ($withDetails) {
            $query->withDefaultRelations();
        }

        return $query->find($id);
    }

    public function createWithDetails(array $data, array $details): Maintenance
    {

        return DB::transaction(function () use ($data, $details) {
           // $data['created_by']     = Auth::id();
            $data['montant_global'] = collect($details)->sum('prix');
            unset($data['details']);
           // dd($data,$details);
            /** @var Maintenance $maintenance */
            $maintenance = $this->model->create($data);

            foreach ($details as $index => $detail) {
                $detail['maintenance_id'] = $maintenance->maintenance_id;
                $maintenance->details()->create($this->prepareDetail($detail));
            }

            return $maintenance->load('details');
        });
    }

    public function updateWithDetails(string $id, array $data, array $details = []): Maintenance
    {
        return DB::transaction(function () use ($id, $data, $details) {
            $maintenance = $this->model->findOrFail($id);

            $data['updated_by'] = Auth::id();

            if (!empty($details)) {
                $data['montant_global'] = collect($details)->sum('prix');

                // Soft-delete les anciens détails
                $maintenance->details()->each(function (MaintenanceDetail $d) {
                    $d->deleted_by = Auth::id();
                    $d->save();
                    $d->delete();
                });

                foreach ($details as $detail) {

                    $maintenance->details()->create($this->prepareDetail($detail));
                }
            }

            $maintenance->update($data);

            return $maintenance->load('details');
        });
    }

    public function updateStatut(string $id, string $statut): Maintenance
    {
        $maintenance = $this->model->findOrFail($id);
        $maintenance->update([
            'statut'     => $statut,
            'updated_by' => Auth::id(),
        ]);

        return $maintenance;
    }

    public function findByProprietaire(string $proprietaireId): Collection
    {
        return $this->model->withDefaultRelations()
            ->where('proprietaire_id', $proprietaireId)
            ->get();
    }

    public function findByLot(string $lotId): Collection
    {
        return $this->model->withDefaultRelations()
            ->where('lot_id', $lotId)
            ->get();
    }

    public function findByBatiment(string $batimentId): Collection
    {
        return $this->model->withDefaultRelations()
            ->where('batiment_id', $batimentId)
            ->get();
    }

    public function findByPorte(string $porteId): Collection
    {
        return $this->model->withDefaultRelations()
            ->where('porte_id', $porteId)
            ->get();
    }

    // =========================================================================
    // Helpers privés
    // =========================================================================

    private function applyFilters($query, array $filters)
    {
        if (!empty($filters['statut'])) {
            $query->where('statut', $filters['statut']);
        }

        if (!empty($filters['proprietaire_id'])) {
            $query->where('proprietaire_id', $filters['proprietaire_id']);
        }

        if (!empty($filters['lot_id'])) {
            $query->where('lot_id', $filters['lot_id']);
        }

        if (!empty($filters['batiment_id'])) {
            $query->where('batiment_id', $filters['batiment_id']);
        }

        if (!empty($filters['prise_en_charge_par'])) {
            $query->where('prise_en_charge_par', $filters['prise_en_charge_par']);
        }

        if (!empty($filters['search'])) {
            $query->where('titre', 'like', '%' . $filters['search'] . '%');
        }

        return $query;
    }

    /**
     * Normalise les clés d'un détail issu du formulaire.
     * Le formulaire utilise "prix" ; la table stocke "montant".
     */
    private function prepareDetail(array $detail): array
    {
      //  dd($detail);
        return [
            'type_intervention_id' => $detail['type_intervention_id'] ?? null,
            'maintenancier_id'     => $detail['maintenancier_id']     ?? null,
            'date_debut'           => $detail['date_debut']           ?? null,
            'date_fin'             => $detail['date_fin']             ?? null,
            'priorite'             => $detail['priorite']             ?? 'normale',
            'montant'              => $detail['prix']                 ?? $detail['montant'] ?? 0,
            'note'                 => $detail['description']          ?? $detail['note']    ?? null,
            'statut'               => $detail['statut']               ?? 'en_attente',
          //  'created_by'           => Auth::id(),
        ];
    }
}
