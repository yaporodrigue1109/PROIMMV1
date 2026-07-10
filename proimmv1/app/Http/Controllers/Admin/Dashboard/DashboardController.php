<?php

namespace App\Http\Controllers\Admin\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $admin = Auth::guard('admin')->user();

        return Inertia::render('Admin/Dashboard/Index', [
            'admin' => $admin?->only(['id_admin', 'name', 'email', 'phone', 'statut']),
            'kpis' => [
                ['label' => 'Revenu du mois', 'value' => '174 000 FCFA', 'trend' => '+12 %', 'tone' => 'success'],
                ['label' => 'Abonnements actifs', 'value' => '6', 'trend' => '+2', 'tone' => 'info'],
                ['label' => 'Paiements en attente', 'value' => '3', 'trend' => '72 000 FCFA', 'tone' => 'warning'],
                ['label' => 'Alertes ouvertes', 'value' => '2', 'trend' => 'Urgent', 'tone' => 'danger'],
            ],
        ]);
    }
}
