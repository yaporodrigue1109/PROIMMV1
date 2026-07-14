<?php

namespace App\Http\Controllers\Admin\Abonnement;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class AbonnementController extends Controller
{
    public function index(): Response
    {
        $abonnements = $this->mockAbonnements();

        return Inertia::render('Admin/Abonnements/Index', [
            'abonnements' => $abonnements,
            'plans' => $this->mockPlans(),
            'stats' => $this->buildStats($abonnements),
            'nextRenewals' => $abonnements
                ->sortBy('date_fin')
                ->take(3)
                ->values(),
        ]);
    }

    public function plans(): Response
    {
        return Inertia::render('Admin/Abonnements/Plans', [
            'plans' => $this->mockPlans(),
            'stats' => $this->buildStats($this->mockAbonnements()),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Abonnements/Form', [
            'mode' => 'create',
            'abonnement' => null,
            'plans' => $this->mockPlans(),
        ]);
    }

    public function show($codeAgence): Response
    {
        $abonnement = $this->findAbonnement($codeAgence);

        abort_if(!$abonnement, 404, 'Abonnement introuvable.');

        return Inertia::render('Admin/Abonnements/Show', [
            'abonnement' => $abonnement,
            'history' => $this->buildHistory($abonnement),
            'plans' => $this->mockPlans(),
        ]);
    }

    public function edit($codeAgence): Response
    {
        $abonnement = $this->findAbonnement($codeAgence);

        abort_if(!$abonnement, 404, 'Abonnement introuvable.');

        return Inertia::render('Admin/Abonnements/Form', [
            'mode' => 'edit',
            'abonnement' => $abonnement,
            'plans' => $this->mockPlans(),
        ]);
    }

    private function findAbonnement(string $codeAgence): ?array
    {
        return $this->mockAbonnements()
            ->firstWhere('code_agence', $codeAgence);
    }

    private function mockAbonnements()
    {
        return collect([
            [
                'agence' => 'Pros Immobilier Cocody',
                'code_agence' => 'AGC-001',
                'plan' => 'Premium',
                'montant' => 45000,
                'cycle' => 'Mensuel',
                'date_debut' => '2026-04-01',
                'date_fin' => '2026-04-30',
                'statut' => 'Actif',
                'modules' => ['Annonces illimitees', 'SMS', 'WhatsApp', 'Statistiques', 'Support prioritaire'],
                'notes' => 'Agence prioritaire avec modules de communication actifs.',
                'created_at' => '2026-04-01 09:40:00',
            ],
            [
                'agence' => 'Pros Immobilier Plateau',
                'code_agence' => 'AGC-002',
                'plan' => 'Standard',
                'montant' => 25000,
                'cycle' => 'Mensuel',
                'date_debut' => '2026-04-05',
                'date_fin' => '2026-05-05',
                'statut' => 'Actif',
                'modules' => ['Annonces standard', 'SMS', 'Rapports simples'],
                'notes' => 'Suivi mensuel standard.',
                'created_at' => '2026-04-05 12:08:00',
            ],
            [
                'agence' => 'Pros Immobilier Yopougon',
                'code_agence' => 'AGC-003',
                'plan' => 'Essentiel',
                'montant' => 15000,
                'cycle' => 'Mensuel',
                'date_debut' => '2026-03-01',
                'date_fin' => '2026-03-31',
                'statut' => 'Expire',
                'modules' => ['Annonces limitees', 'Support email'],
                'notes' => 'Renouvellement a relancer.',
                'created_at' => '2026-03-01 08:22:00',
            ],
            [
                'agence' => 'Pros Immobilier Bingerville',
                'code_agence' => 'AGC-004',
                'plan' => 'Premium',
                'montant' => 45000,
                'cycle' => 'Mensuel',
                'date_debut' => '2026-04-15',
                'date_fin' => '2026-05-15',
                'statut' => 'En attente',
                'modules' => ['Annonces illimitees', 'SMS', 'WhatsApp', 'Statistiques'],
                'notes' => 'Paiement en attente de confirmation.',
                'created_at' => '2026-04-15 17:15:00',
            ],
        ]);
    }

    private function mockPlans(): array
    {
        return [
            [
                'nom' => 'Essentiel',
                'prix' => 15000,
                'description' => 'Pour les petites agences qui demarrent.',
                'cycle' => 'Mensuel',
                'modules' => ['Annonces limitees', 'Support email', 'Tableau de bord simple'],
                'highlight' => false,
            ],
            [
                'nom' => 'Standard',
                'prix' => 25000,
                'description' => 'Pour suivre les agences actives au quotidien.',
                'cycle' => 'Mensuel',
                'modules' => ['Annonces standard', 'SMS', 'Rapports simples', 'Gestion equipe'],
                'highlight' => true,
            ],
            [
                'nom' => 'Premium',
                'prix' => 45000,
                'description' => 'Pour les agences qui veulent tous les leviers.',
                'cycle' => 'Mensuel',
                'modules' => ['Annonces illimitees', 'SMS', 'WhatsApp', 'Statistiques', 'Support prioritaire'],
                'highlight' => false,
            ],
        ];
    }

    private function buildStats($abonnements): array
    {
        $items = collect($abonnements);

        return [
            'total' => $items->count(),
            'actifs' => $items->where('statut', 'Actif')->count(),
            'attente' => $items->where('statut', 'En attente')->count(),
            'expires' => $items->where('statut', 'Expire')->count(),
            'revenu' => $items->where('statut', 'Actif')->sum('montant'),
        ];
    }

    private function buildHistory(array $abonnement): array
    {
        $dateFin = \Carbon\Carbon::parse($abonnement['date_fin']);

        return [
            [
                'periode' => $dateFin->copy()->subMonths(2)->format('d/m/Y'),
                'montant' => $abonnement['montant'],
                'statut' => 'Paye',
            ],
            [
                'periode' => $dateFin->copy()->subMonth()->format('d/m/Y'),
                'montant' => $abonnement['montant'],
                'statut' => 'Paye',
            ],
            [
                'periode' => $dateFin->format('d/m/Y'),
                'montant' => $abonnement['montant'],
                'statut' => $abonnement['statut'] === 'Actif' ? 'Paye' : 'A confirmer',
            ],
        ];
    }
}
