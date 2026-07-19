<?php

namespace App\Repositories\Agence;

use App\Models\VenteBien;
use App\Repositories\Agence\Interfaces\VenteBienRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class VenteBienRepository implements VenteBienRepositoryInterface
{
    protected VenteBien $model;

    public function __construct(VenteBien $model)
    {
        $this->model = $model;
    }

    /**
     * {@inheritDoc}
     */
    public function getAll(): Collection
    {
        return $this->model->with(['propriete', 'batiment', 'porte', 'proprietaire', 'acheteur'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * {@inheritDoc}
     */
    public function findById(string $id): ?object
    {
        return $this->model->with(['propriete', 'batiment', 'porte', 'proprietaire', 'acheteur'])
            ->find($id);
    }

    /**
     * {@inheritDoc}
     */
    public function create(array $data): object
    {
        return $this->model->create($data);
    }

    /**
     * {@inheritDoc}
     */
    public function update(string $id, array $data): bool
    {
        $vente = $this->findById($id);
        if (!$vente) {
            return false;
        }
        return $vente->update($data);
    }

    /**
     * {@inheritDoc}
     */
    public function delete(string $id): bool
    {
        $vente = $this->findById($id);
        if (!$vente) {
            return false;
        }
        return $vente->delete();
    }

    /**
     * {@inheritDoc}
     */
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->with(['propriete', 'batiment', 'porte', 'proprietaire', 'acheteur'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * {@inheritDoc}
     */
    public function getByAgence(string $agenceId): Collection
    {
        return $this->model->with(['propriete', 'batiment', 'porte', 'proprietaire', 'acheteur'])
            ->where('agence_id', $agenceId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * {@inheritDoc}
     */
    public function getByProprietaire(string $proprietaireId): Collection
    {
        return $this->model->with(['propriete', 'batiment', 'porte', 'acheteur'])
            ->where('proprietaire_id', $proprietaireId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * {@inheritDoc}
     */
    public function getByAcheteur(string $acheteurId): Collection
    {
        return $this->model->with(['propriete', 'batiment', 'porte', 'proprietaire'])
            ->where('acheteur_vente_id', $acheteurId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * {@inheritDoc}
     */
    public function getByStatut(string $statut): Collection
    {
        return $this->model->with(['propriete', 'batiment', 'porte', 'proprietaire', 'acheteur'])
            ->where('statut', $statut)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * {@inheritDoc}
     */
    public function getByTypePaiement(string $typePaiement): Collection
    {
        return $this->model->with(['propriete', 'batiment', 'porte', 'proprietaire', 'acheteur'])
            ->where('type_paiement', $typePaiement)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * {@inheritDoc}
     */
    public function getEnCours(): Collection
    {
        return $this->model->with(['propriete', 'batiment', 'porte', 'proprietaire', 'acheteur'])
            ->where('statut', VenteBien::STATUT_EN_COURS)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * {@inheritDoc}
     */
    public function getTermine(): Collection
    {
        return $this->model->with(['propriete', 'batiment', 'porte', 'proprietaire', 'acheteur'])
            ->where('statut', VenteBien::STATUT_TERMINE)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * {@inheritDoc}
     */
    public function getAnnule(): Collection
    {
        return $this->model->with(['propriete', 'batiment', 'porte', 'proprietaire', 'acheteur'])
            ->where('statut', VenteBien::STATUT_ANNULE)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * {@inheritDoc}
     */
    public function getFiltered(array $filters = []): Collection
    {
        $query = $this->model->with(['propriete', 'batiment', 'porte', 'proprietaire', 'acheteur']);

        if (isset($filters['agence_id'])) {
            $query->where('agence_id', $filters['agence_id']);
        }

        if (isset($filters['proprietaire_id'])) {
            $query->where('proprietaire_id', $filters['proprietaire_id']);
        }

        if (isset($filters['acheteur_id'])) {
            $query->where('acheteur_vente_id', $filters['acheteur_id']);
        }

        if (isset($filters['statut'])) {
            $query->where('statut', $filters['statut']);
        }

        if (isset($filters['type_paiement'])) {
            $query->where('type_paiement', $filters['type_paiement']);
        }

        if (isset($filters['date_from']) && isset($filters['date_to'])) {
            $query->whereBetween('date_accord', [$filters['date_from'], $filters['date_to']]);
        }

        if (isset($filters['prix_min'])) {
            $query->where('prix_vente', '>=', $filters['prix_min']);
        }

        if (isset($filters['prix_max'])) {
            $query->where('prix_vente', '<=', $filters['prix_max']);
        }

        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('reference', 'like', "%{$filters['search']}%")
                  ->orWhereHas('proprietaire', function ($sub) use ($filters) {
                      $sub->where('name', 'like', "%{$filters['search']}%");
                  })
                  ->orWhereHas('acheteur', function ($sub) use ($filters) {
                      $sub->where('name', 'like', "%{$filters['search']}%");
                  });
            });
        }

        if (isset($filters['with_relations']) && is_array($filters['with_relations'])) {
            $query->with($filters['with_relations']);
        }

        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        $query->orderBy($sortBy, $sortOrder);

        return $query->get();
    }

    /**
     * {@inheritDoc}
     */
    public function getStatisticsByAgence(string $agenceId): array
    {
        $stats = $this->model->where('agence_id', $agenceId)
            ->select([
                DB::raw('COUNT(*) as total_ventes'),
                DB::raw('SUM(prix_vente) as total_prix_ventes'),
                DB::raw('SUM(commission) as total_commissions'),
                DB::raw('SUM(montant_proprietaire) as total_montant_proprietaires'),
                DB::raw('COUNT(CASE WHEN statut = "termine" THEN 1 END) as ventes_terminees'),
                DB::raw('COUNT(CASE WHEN statut = "en_cours" THEN 1 END) as ventes_en_cours'),
                DB::raw('COUNT(CASE WHEN statut = "annule" THEN 1 END) as ventes_annulees'),
                DB::raw('AVG(prix_vente) as prix_moyen'),
            ])
            ->first()
            ->toArray();

        return $stats;
    }

    /**
     * {@inheritDoc}
     */
    public function getTotalVentesByAgence(string $agenceId): float
    {
        return $this->model->where('agence_id', $agenceId)
            ->where('statut', VenteBien::STATUT_TERMINE)
            ->sum('prix_vente') ?? 0;
    }

    /**
     * {@inheritDoc}
     */
    public function getTotalCommissionsByAgence(string $agenceId): float
    {
        return $this->model->where('agence_id', $agenceId)
            ->where('statut', VenteBien::STATUT_TERMINE)
            ->sum('commission') ?? 0;
    }

    /**
     * {@inheritDoc}
     */
    public function generateReference(string $agenceId): string
    {
        // Récupérer la dernière référence
        $lastVente = $this->model->where('agence_id', $agenceId)
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$lastVente) {
            $number = 1;
        } else {
            $lastRef = $lastVente->reference;
            $parts = explode('-', $lastRef);
            $number = (int) end($parts) + 1;
        }

        $year = date('Y');
        return sprintf('VTE-%s-%04d', $year, $number);
    }
}