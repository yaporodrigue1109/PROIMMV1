<?php

namespace App\Http\Controllers\Agence\Abonnement;

use App\Http\Controllers\Controller;
use App\Services\AgenceService;
use App\Services\ConfigurationTarifService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class AbonnementController extends Controller
{
    public function __construct(
        protected ConfigurationTarifService $tarifService,
        protected AgenceService $agenceService
    ) {
    }

    public function index(): Response|RedirectResponse
    {
        $user = Auth::guard('user')->user();
        $agence = $user?->agence()->with(['abonnement'])->first();

        if ($this->hasActiveSubscription($agence)) {
            return redirect()
                ->route('agence.dashboard')
                ->with('success', 'Votre abonnement est déjà actif.');
        }

        return Inertia::render('Agence/Abonnement/Index', [
            'tarifs' => $this->tarifService->getTarifsPublics(),
            'draft' => session('agence_subscription_draft'),
        ]);
    }

    public function checkout(Request $request): RedirectResponse
    {
        $tarifs = $this->tarifService->getTarifsPublics();
        $allowedDurations = collect($tarifs['durees'] ?? [])->pluck('nombre_mois')->filter()->map(fn ($value) => (int) $value)->all();
        $allowedModuleIds = collect($tarifs['modules'] ?? [])->pluck('id')->filter()->map(fn ($value) => (int) $value)->all();

        $validated = $request->validate([
            'duree_mois' => ['required', 'integer', Rule::in($allowedDurations)],
            'modules' => ['array'],
            'modules.*' => ['integer', Rule::in($allowedModuleIds)],
        ]);

        $modulesIds = collect($validated['modules'] ?? [])
            ->map(fn ($moduleId) => (int) $moduleId)
            ->filter(fn ($moduleId) => $moduleId > 0)
            ->values()
            ->all();

        $pricing = $this->tarifService->calculerPrixAgence((int) $validated['duree_mois'], $modulesIds);

        session()->put('agence_subscription_draft', array_merge($pricing, [
            'duree_mois' => (int) $validated['duree_mois'],
            'modules_ids' => $modulesIds,
        ]));

        return redirect()->route('agence.abonnement.paiement');
    }

    public function payment(): Response|RedirectResponse
    {
        $draft = session('agence_subscription_draft');

        if (empty($draft)) {
            return redirect()
                ->route('agence.abonnement.index')
                ->with('error', 'Commencez par choisir votre formule.');
        }

        return Inertia::render('Agence/Abonnement/Paiement', [
            'draft' => $draft,
            'tarifs' => $this->tarifService->getTarifsPublics(),
        ]);
    }

    public function testValidate(Request $request): RedirectResponse
    {
        $user = Auth::guard('user')->user();
        $agence = $user?->agence()->with(['abonnement'])->first();

        if (!$agence) {
            return redirect()
                ->route('agence.abonnement.index')
                ->with('error', 'Agence introuvable.');
        }

        $draft = session('agence_subscription_draft');
        if (empty($draft)) {
            return redirect()
                ->route('agence.abonnement.index')
                ->with('error', 'Commencez par choisir votre formule.');
        }

        $validated = $request->validate([
            'mode_paiement' => ['nullable', 'string', 'max:50'],
        ]);

        $this->agenceService->validateSubscriptionDraft(
            $agence,
            $draft,
            (string) ($user?->id_users ?? 'system'),
            $validated['mode_paiement'] ?? 'test'
        );

        session()->forget('agence_subscription_draft');

        return redirect()
            ->route('agence.dashboard')
            ->with('success', 'Abonnement validé avec succès.');
    }

    private function hasActiveSubscription($agence): bool
    {
        if (!$agence?->abonnement_id || !$agence?->abonnement_end) {
            return false;
        }

        return $agence->abonnement_end->greaterThanOrEqualTo(now()->startOfDay());
    }
}
