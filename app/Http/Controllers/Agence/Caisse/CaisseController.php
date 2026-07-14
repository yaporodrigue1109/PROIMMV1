<?php

namespace App\Http\Controllers\Agence\Caisse;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;

class CaisseController extends Controller
{
    public function index()
    {
        return Inertia::render('Agence/Caisse/Index', [
            'caisseOuverte' => true,
        ]);
    }

    public function maintenance()
    {
        $agenceId = $this->agenceId();

        return Inertia::render('Agence/Caisse/Maintenance', [
            'maintenances' => $this->safeMaintenanceRows($agenceId),
            'proprietaires' => $this->safeTableRows('proprietaires', ['proprietaire_id', 'name']),
            'lots' => $this->safeTableRows('propietaire_lots', ['propreietaire_lot_id', 'name', 'proprietaire_id']),
            'batiments' => $this->safeTableRows('batiment', ['batiment_id', 'name', 'propriete_id']),
            'portes' => $this->safeTableRows('porte', ['porte_id', 'numero_porte', 'batiment_id']),
            'typesIntervention' => $this->safeTableRows('type_maintenances', ['type_maintenance_id', 'name', 'description']),
            'maintenanciers' => $this->safeTableRows('maintenanciers', ['maintenancier_id', 'name', 'fonction_maintenance_id']),
        ]);
    }

    public function depenseAgence()
    {
        return Inertia::render('Agence/Caisse/DepenseAgence', [
            'caisseOuverte' => true,
        ]);
    }

    public function venteBien()
    {
        return Inertia::render('Agence/Caisse/VenteBien', [
            'caisseOuverte' => true,
        ]);
    }

    private function safeMaintenanceRows(string $agenceId): array
    {
        if (! Schema::hasTable('maintenance')) {
            return [];
        }

        return DB::table('maintenance')
            ->where('agence_id', $agenceId)
            ->orderByDesc('created_at')
            ->limit(50)
            ->get([
                'maintenance_id',
                'titre',
                'description',
                'statut',
                'montant_global',
                'proprietaire_id',
                'lot_id',
                'batiment_id',
                'porte_id',
                'prise_en_charge_par',
                'created_at',
            ])
            ->map(function ($row) {
                return [
                    'maintenance_id' => $row->maintenance_id,
                    'titre' => $row->titre,
                    'description' => $row->description,
                    'statut' => $row->statut,
                    'montant_global' => $row->montant_global,
                    'proprietaire_id' => $row->proprietaire_id,
                    'lot_id' => $row->lot_id,
                    'batiment_id' => $row->batiment_id,
                    'porte_id' => $row->porte_id,
                    'prise_en_charge_par' => $row->prise_en_charge_par,
                    'created_at' => $row->created_at,
                ];
            })
            ->toArray();
    }

    private function safeTableRows(string $table, array $columns): array
    {
        if (! Schema::hasTable($table)) {
            return [];
        }

        return DB::table($table)
            ->orderBy($columns[1] ?? $columns[0])
            ->get($columns)
            ->toArray();
    }

    private function agenceId(): string
    {
        return getInfoAgent()->users->agence_id;
    }
}
