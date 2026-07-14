<?php

namespace App\Http\Controllers\Agence\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $agence = Auth::guard('user')->user();

        $stats = [
            'proprietes' => 295,
            'proprietes_vente' => 0,
            'proprietes_location' => 0,
            'proprietaires' => 0,
            'locataires' => 724,
            'ventes' => 0,
            'loyers' => 0,
            'reversements' => 0,
            'caisse' => 0,
            'depenses' => 0,
            'maintenances' => 0,
        ];

        return Inertia::render('Agence/Dashboard/Index', [
            'agence' => $agence?->only([
                'id_users',
                'name',
                'email',
                'phone',
                'statut',
            ]),
            'stats' => $stats,
        ]);
    }
}
