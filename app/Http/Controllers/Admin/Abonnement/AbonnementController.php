<?php

namespace App\Http\Controllers\Admin\Abonnement;

use App\Http\Controllers\Controller;
use App\Models\Abonnement;
use App\Models\AbonnementHistorique;
use App\Services\AgenceService;
use App\Services\ConfigurationTarifService;
use App\Repositories\Interfaces\AbonnementRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AbonnementController extends Controller
{
    public function __construct(
        protected AgenceService $agenceService,
        protected ConfigurationTarifService $tarifService,
        protected AbonnementRepositoryInterface $abonnementRepository
    ) {}

    public function index(): Response
    {
        $abonnements = $this->getSubscriptionItems();
        $plans = $this->getPlans();

        return Inertia::render('Admin/Abonnements/Index', [
            'abonnements' => $abonnements,
            'plans' => $plans,
            'stats' => $this->buildStats($abonnements),
            'nextRenewals' => $abonnements
                ->sortBy('date_fin')
                ->take(3)
                ->values(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Abonnements/Form', [
            'mode' => 'create',
            'agence' => null,
            'agences' => $this->getAgencesForSubscription(),
            'tarifs' => $this->tarifService->getTarifsPourFormulaire(),
        ]);
    }

    public function show($codeAgence): Response
    {
        $abonnement = $this->findSubscription($codeAgence);

        abort_if(!$abonnement, 404, 'Abonnement introuvable.');

        return Inertia::render('Admin/Abonnements/Show', [
            'abonnement' => $abonnement,
            'history' => $this->buildHistory($abonnement['agence_id'] ?? null),
            'plans' => $this->getPlans(),
        ]);
    }

    public function edit($codeAgence): Response
    {
        $agence = $this->findAgency($codeAgence);

        abort_if(!$agence, 404, 'Agence introuvable.');

        return Inertia::render('Admin/Abonnements/Form', [
            'mode' => 'edit',
            'agence' => $agence,
            'agences' => $this->getAgencesForSubscription(),
            'tarifs' => $this->tarifService->getTarifsPourFormulaire(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        return $this->saveSubscription($request, false);
    }

    public function update(Request $request, string $codeAgence): RedirectResponse
    {
        return $this->saveSubscription($request, true, $codeAgence);
    }

    private function findAgency(string $codeAgence): ?object
    {
        $agence = $this->agenceService->findByCode($codeAgence);

        if (!$agence) {
            return null;
        }

        return $this->agenceService->findWithRelations($agence->agence_id) ?? $agence;
    }

    private function findSubscription(string $codeAgence): ?array
    {
        $agence = $this->findAgency($codeAgence);

        if (!$agence) {
            return null;
        }

        $snapshot = Abonnement::query()
            ->where('type', 'subscription')
            ->with(['nouvelAbonnement'])
            ->where('agence_id', $agence->agence_id)
            ->first();

        return $this->mapSubscriptionItem($agence, $snapshot);
    }

    private function saveSubscription(Request $request, bool $isUpdate, ?string $codeAgence = null): RedirectResponse
    {
        try {
            $data = $request->validate([
                'agence_id' => ['required', 'string', 'exists:agences,agence_id'],
                'abonnement_start' => ['required', 'date'],
                'abonnement_end' => ['required', 'date', 'after:abonnement_start'],
                'duree_mois' => ['required', 'integer', 'min:1'],
                'montant_base_total' => ['required', 'numeric', 'min:0'],
                'montant_total' => ['required', 'numeric', 'min:0'],
                'options' => ['nullable', 'array'],
                'options.*' => ['integer'],
                'abonnement_notes' => ['nullable', 'string', 'max:500'],
            ]);

            $agenceId = $data['agence_id'];
            unset($data['agence_id']);
            $data['statut'] = 'active';

            $agence = $this->agenceService->updateAgence($agenceId, $data);

            return redirect()
                ->route('admin.abonnements.show', $agence->code_agence)
                ->with(
                    'success',
                    $isUpdate
                        ? 'L\'abonnement de l\'agence a été renouvelé avec succès.'
                        : 'L\'agence a été abonnée avec succès.'
                );
        } catch (\Throwable $e) {
            return back()
                ->with('error', 'Erreur lors de l\'enregistrement : ' . $e->getMessage())
                ->withInput();
        }
    }

    private function getAgencesForSubscription()
    {
        return $this->agenceService->getAll([], -1);
    }

    private function getSubscriptionItems()
    {
        $agences = collect($this->agenceService->getAll([], -1));
        $snapshots = Abonnement::query()
            ->where('type', 'subscription')
            ->with(['nouvelAbonnement'])
            ->get()
            ->keyBy('agence_id');

        $items = $agences
            ->filter(fn ($agence) => !empty($agence->abonnement_start) || !empty($agence->abonnement_end) || !empty($agence->abonnement_id))
            ->map(fn ($agence) => $this->mapSubscriptionItem($agence, $snapshots->get($agence->agence_id)))
            ->values();

        return $items;
    }

    private function mapSubscriptionItem(object $agence, ?Abonnement $snapshot = null): array
    {
        $plan = $snapshot?->nouvelAbonnement ?? $agence->abonnement;
        $start = $snapshot?->nouvelle_date_debut ?? $agence->abonnement_start ?? null;
        $end = $snapshot?->nouvelle_date_fin ?? $agence->abonnement_end ?? null;
        $duration = $snapshot?->duree_mois ?? $agence->duree_mois ?? null;
        $amount = $snapshot?->montant_ht ?? $agence->montant_total ?? $plan?->prix_mensuel_ht ?? 0;
        $notes = $snapshot?->notes ?? null;

        return [
            'agence' => $agence->name ?? 'Agence sans nom',
            'code_agence' => $agence->code_agence ?? null,
            'plan' => $plan?->name ?? 'Plan unique',
            'plan_label' => $plan?->name ?? 'Plan unique',
            'plan_description' => $plan?->description ?? 'Aucune description disponible.',
            'plan_modules' => $this->extractModules($plan?->features ?? []),
            'montant' => (float) $amount,
            'cycle' => $duration ? "{$duration} mois" : 'Mensuel',
            'date_debut' => $this->formatDateValue($start),
            'date_fin' => $this->formatDateValue($end),
            'statut' => $this->resolveStatus($start, $end),
            'paiement' => $this->resolvePaymentStatus($start, $end),
            'modules' => $this->extractModules($plan?->features ?? []),
            'notes' => $notes,
            'created_at' => optional($snapshot?->created_at ?? $agence->created_at)?->format('Y-m-d H:i:s'),
        ];
    }

    private function getPlans(): array
    {
        return $this->abonnementRepository
            ->getActifs()
            ->map(function (Abonnement $plan) {
                return [
                    'nom' => $plan->name,
                    'prix' => $plan->prix_mensuel_ht,
                    'description' => $plan->description,
                    'cycle' => 'Mensuel',
                    'modules' => $this->extractModules($plan->features ?? []),
                    'highlight' => (bool) $plan->is_default,
                ];
            })
            ->values()
            ->all();
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

    private function buildHistory(?string $agenceId): array
    {
        if (!$agenceId) {
            return [];
        }

        return AbonnementHistorique::query()
            ->where('agence_id', $agenceId)
            ->orderByDesc('created_at')
            ->take(12)
            ->get()
            ->map(function (AbonnementHistorique $historique) {
                $periode = trim(
                    $this->formatDateValue($historique->nouvelle_date_debut) .
                    ' - ' .
                    $this->formatDateValue($historique->nouvelle_date_fin)
                );

                return [
                    'periode' => $periode,
                    'montant' => $historique->montant_ht,
                    'statut' => $historique->action === 'annulation' ? 'A confirmer' : 'Paye',
                ];
            })
            ->values()
            ->all();
    }

    private function resolveStatus($start, $end): string
    {
        if (!$start || !$end) {
            return 'En attente';
        }

        $startDate = Carbon::parse($start);
        $endDate = Carbon::parse($end);

        if (now()->lt($startDate)) {
            return 'En attente';
        }

        if (now()->gt($endDate)) {
            return 'Expire';
        }

        return 'Actif';
    }

    private function resolvePaymentStatus($start, $end): string
    {
        if (!$start || !$end) {
            return 'A confirmer';
        }

        return $this->resolveStatus($start, $end) === 'Expire' ? 'A confirmer' : 'Paye';
    }

    private function formatDateValue($value): ?string
    {
        if (empty($value)) {
            return null;
        }

        try {
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
    }

    private function extractModules($features): array
    {
        if (is_string($features)) {
            $decoded = json_decode($features, true);
            $features = json_last_error() === JSON_ERROR_NONE ? $decoded : [$features];
        }

        if (!is_array($features)) {
            return [];
        }

        return collect($features)
            ->map(function ($item) {
                if (is_string($item)) {
                    return trim($item);
                }

                if (is_array($item)) {
                    return $item['label']
                        ?? $item['name']
                        ?? $item['nom']
                        ?? $item['libelle']
                        ?? null;
                }

                return null;
            })
            ->filter()
            ->values()
            ->all();
    }
}
