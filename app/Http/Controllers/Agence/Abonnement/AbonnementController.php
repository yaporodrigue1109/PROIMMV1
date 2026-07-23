<?php

namespace App\Http\Controllers\Agence\Abonnement;

use App\Http\Controllers\Controller;
use App\Models\Agence;
use App\Models\Transaction;
use App\Services\AgenceService;
use App\Services\ConfigurationTarifService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
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

    public function index(Request $request): Response|RedirectResponse
    {
        $user = Auth::guard('user')->user();
        $agence = $user?->agence()->with(['abonnement'])->first();

        if ($this->hasActiveSubscription($agence) && ! $request->boolean('renew')) {
            return redirect()
                ->route('agence.dashboard')
                ->with('success', 'Votre abonnement est déjà actif.');
        }

        $subscriptionFlow = $this->buildSubscriptionActionContext($agence, $request->boolean('renew'));

        return Inertia::render('Agence/Abonnement/Index', [
            'tarifs' => $this->tarifService->getTarifsPublics(),
            'draft' => session('agence_subscription_draft'),
            'subscription_flow' => $subscriptionFlow,
        ]);
    }

    public function consultation(Request $request): Response
    {
        $user = Auth::guard('user')->user();
        $agence = $user?->agence()->with([
            'abonnement',
            'abonnementHistoriques' => function ($query) {
                $query->with(['transaction', 'nouvelAbonnement', 'ancienAbonnement'])
                    ->latest('created_at');
            },
        ])->first();

        $page = max(1, (int) $request->integer('page', 1));

        return Inertia::render('Agence/Abonnement/Consultation', [
            'consultation' => $this->buildConsultationPayload($agence, $page),
        ]);
    }

    public function receipt(Transaction $transaction)
    {
        $user = Auth::guard('user')->user();
        $agenceId = $user?->agence_id;

        abort_if(!$agenceId || $transaction->agence_id !== $agenceId, 403);

        $transaction->load(['agence', 'abonnement', 'abonnementHistorique']);

        $filename = 'recu-abonnement-' . $transaction->reference . '.pdf';
        $pdfBinary = $this->buildReceiptPdf($transaction, $user?->name ?? 'Agence');

        return response($pdfBinary, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    public function checkout(Request $request): RedirectResponse
    {
        $user = Auth::guard('user')->user();
        $agence = $user?->agence()->with(['abonnement'])->first();

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
        $subscriptionContext = $this->buildSubscriptionActionContext($agence, true);

        session()->put('agence_subscription_draft', array_merge($pricing, [
            'duree_mois' => (int) $validated['duree_mois'],
            'modules_ids' => $modulesIds,
            'subscription_context' => $subscriptionContext,
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

    private function buildConsultationPayload(?Agence $agence, int $page = 1): array
    {
        $subscription = $agence?->abonnement;
        $historyRows = $this->deduplicateSubscriptionHistories($agence?->abonnementHistoriques ?? collect());

        $payments = $historyRows->map(function ($row) use ($subscription) {
            $transaction = $row->transaction;
            $paymentStatus = $transaction?->statut ?? 'en_attente';
            $paymentDate = $transaction?->date_paiement ?? $row->created_at;

            return [
                'id' => $row->id,
                'period_start' => $this->formatDateForClient($row->nouvelle_date_debut),
                'period_end' => $this->formatDateForClient($row->nouvelle_date_fin),
                'period_label' => $this->formatPeriodLabel($row->nouvelle_date_debut, $row->nouvelle_date_fin),
                'plan_name' => $row->nouvelAbonnement?->name ?? $subscription?->name ?? 'Abonnement',
                'amount' => (float) ($transaction?->montant_ttc ?? $row->montant_ht ?? 0),
                'status' => $paymentStatus,
                'status_label' => match ($paymentStatus) {
                    'validee' => 'Validée',
                    'en_attente' => 'En attente',
                    'echouee' => 'Échouée',
                    'annulee' => 'Annulée',
                    'remboursee' => 'Remboursée',
                    default => ucfirst((string) $paymentStatus),
                },
                'mode_paiement' => $transaction?->mode_paiement ?? 'Non renseigné',
                'mode_label' => $this->formatPaymentModeLabel($transaction?->mode_paiement),
                'payment_detail' => $this->formatPaymentDetail($transaction),
                'reference' => $transaction?->reference_paiement ?? $transaction?->reference ?? '—',
                'paid_at' => $this->formatDateTimeForClient($paymentDate),
                'action' => $row->action,
                'notes' => $row->notes,
                'receipt_url' => $transaction
                    ? route('agence.abonnement.receipt', $transaction->getKey())
                    : null,
            ];
        })->values();

        $latestPayment = $payments->first();
        $paymentsPaginator = $this->paginateConsultationPayments($payments, 6, $page);

        $daysRemaining = (int) ($agence?->jours_restants_abonnement ?? 0);
        $renewalAlert = null;

        if ($agence?->abonnement_end && $daysRemaining <= 15) {
            $renewalAlert = [
                'tone' => $daysRemaining <= 7 ? 'danger' : 'warning',
                'title' => $daysRemaining <= 0 ? 'Abonnement expiré' : 'Renouvellement recommandé',
                'message' => $daysRemaining <= 0
                    ? 'Votre abonnement est arrivé à expiration. Renouvelez-le pour éviter toute interruption.'
                    : "Il reste {$daysRemaining} jour(s) avant l'expiration. Il vaut mieux anticiper le renouvellement.",
            ];
        }

        $supportPhone = company_phone();
        $supportEmail = company_email();
        $primaryAction = $this->buildSubscriptionActionContext($agence);

        return [
            'agency_name' => $agence?->name ?? 'Mon agence',
            'agency_code' => $agence?->code_agence,
            'is_active' => $this->hasActiveSubscription($agence),
            'status_label' => $this->hasActiveSubscription($agence) ? 'Actif' : 'Inactif',
            'renew_url' => $primaryAction['href'],
            'primary_action' => $primaryAction,
            'renewal_alert' => $renewalAlert,
            'plan' => [
                'name' => $subscription?->name ?? 'Aucun abonnement',
                'description' => $subscription?->description ?? 'Aucun abonnement actif pour le moment.',
                'code' => $subscription?->code_abonnement,
                'amount_monthly' => (float) ($subscription?->prix_mensuel_ht ?? 0),
                'amount_yearly' => (float) ($subscription?->prix_annuel_ht ?? 0),
                'current_amount' => (float) ($latestPayment['amount'] ?? $subscription?->montant_ht ?? $subscription?->prix_mensuel_ht ?? 0),
                'start' => $this->formatDateForClient($agence?->abonnement_start),
                'end' => $this->formatDateForClient($agence?->abonnement_end),
                'days_remaining' => (int) ($agence?->jours_restants_abonnement ?? 0),
                'is_expired' => (bool) ($agence?->abonnement_expire ?? false),
                'modules' => $this->extractSubscriptionModules($subscription),
                'features' => array_values(array_filter(collect($subscription?->features ?? [])->flatten()->all())),
            ],
            'summary' => [
                'payments_count' => $paymentsPaginator['total'],
                'validated_payments' => $payments->where('status', 'validee')->count(),
                'pending_payments' => $payments->where('status', 'en_attente')->count(),
                'failed_payments' => $payments->where('status', 'echouee')->count(),
                'total_paid' => (float) $payments->where('status', 'validee')->sum('amount'),
                'latest_payment_date' => $latestPayment['paid_at'] ?? null,
                'latest_payment_status' => $latestPayment['status_label'] ?? 'Aucun paiement',
                'latest_payment_reference' => $latestPayment['reference'] ?? '—',
            ],
            'payments' => $paymentsPaginator,
            'support' => [
                'phone' => $supportPhone ?: null,
                'email' => $supportEmail ?: null,
            ],
        ];
    }

    private function buildSubscriptionActionContext(?Agence $agence, bool $renewMode = false): array
    {
        $hasSubscription = (bool) ($agence?->abonnement_id && $agence?->abonnement);
        $daysRemaining = (int) ($agence?->jours_restants_abonnement ?? 0);
        $isExpired = (bool) ($agence?->abonnement_expire ?? false) || ($hasSubscription && $daysRemaining <= 0);
        $isUrgent = $hasSubscription && $daysRemaining <= 7;

        if (! $hasSubscription) {
            return [
                'state' => 'new',
                'title' => 'Souscrire un abonnement',
                'description' => 'Choisissez la formule et les modules qui correspondent à votre agence.',
                'button_label' => 'Souscrire et payer',
                'href' => '/agence/abonnement',
                'tone' => 'primary',
            ];
        }

        if ($isExpired) {
            return [
                'state' => 'expired',
                'title' => 'Réactiver votre abonnement',
                'description' => 'Votre abonnement est expiré. Vous pouvez repartir sur une nouvelle période immédiatement.',
                'button_label' => 'Réactiver et payer',
                'href' => '/agence/abonnement?renew=1',
                'tone' => 'danger',
            ];
        }

        if ($renewMode || $isUrgent) {
            return [
                'state' => $isUrgent ? 'urgent_renewal' : 'renewal',
                'title' => 'Renouveler votre abonnement',
                'description' => $isUrgent
                    ? 'Il reste peu de temps avant l’échéance. Mieux vaut renouveler maintenant.'
                    : 'Vous pouvez renouveler à l’avance pour éviter toute interruption de service.',
                'button_label' => $isUrgent ? 'Renouveler maintenant' : 'Renouveler à l’avance',
                'href' => '/agence/abonnement?renew=1',
                'tone' => $isUrgent ? 'warning' : 'primary',
            ];
        }

        return [
            'state' => 'renewal',
            'title' => 'Renouveler votre abonnement',
            'description' => 'Vous pouvez anticiper le renouvellement pour garder le service actif sans interruption.',
            'button_label' => 'Renouveler à l’avance',
            'href' => '/agence/abonnement?renew=1',
            'tone' => 'primary',
        ];
    }

    private function paginateConsultationPayments($payments, int $perPage, int $page): array
    {
        $items = collect($payments);
        $total = $items->count();
        $page = max(1, $page);
        $slice = $items->forPage($page, $perPage)->values()->all();
        $lastPage = max(1, (int) ceil($total / $perPage));

        return [
            'data' => $slice,
            'current_page' => $page,
            'last_page' => $lastPage,
            'per_page' => $perPage,
            'total' => $total,
            'from' => $total > 0 ? (($page - 1) * $perPage) + 1 : 0,
            'to' => min($total, $page * $perPage),
            'prev_page_url' => $page > 1
                ? route('agence.abonnement.consultation', ['page' => $page - 1])
                : null,
            'next_page_url' => $page < $lastPage
                ? route('agence.abonnement.consultation', ['page' => $page + 1])
                : null,
        ];
    }

    private function deduplicateSubscriptionHistories($historyRows)
    {
        return collect($historyRows)
            ->sortByDesc(function ($row) {
                $status = $row->transaction?->statut ?? 'en_attente';

                return match ($status) {
                    'validee' => 3,
                    'en_attente' => 2,
                    'echouee' => 1,
                    default => 0,
                };
            })
            ->groupBy(fn ($row) => $this->subscriptionHistorySignature($row))
            ->map(fn ($group) => $group->sortByDesc('created_at')->first())
            ->values();
    }

    private function subscriptionHistorySignature($row): string
    {
        return implode('|', [
            $this->formatDateForClient($row->nouvelle_date_debut) ?? 'null',
            $this->formatDateForClient($row->nouvelle_date_fin) ?? 'null',
            number_format((float) ($row->transaction?->montant_ttc ?? $row->montant_ht ?? 0), 2, '.', ''),
        ]);
    }

    private function formatDateForClient($value): ?string
    {
        if (empty($value)) {
            return null;
        }

        try {
            return Carbon::parse($value)->toDateString();
        } catch (\Throwable) {
            return (string) $value;
        }
    }

    private function formatDateTimeForClient($value): ?string
    {
        if (empty($value)) {
            return null;
        }

        try {
            return Carbon::parse($value)->toIso8601String();
        } catch (\Throwable) {
            return (string) $value;
        }
    }

    private function formatPeriodLabel($start, $end): string
    {
        $startLabel = $start ? Carbon::parse($start)->format('d/m/Y') : '—';
        $endLabel = $end ? Carbon::parse($end)->format('d/m/Y') : '—';

        return $startLabel . ' - ' . $endLabel;
    }

    private function formatPaymentModeLabel(?string $mode): string
    {
        if (empty($mode)) {
            return 'Non renseigné';
        }

        return match ($mode) {
            'mobile_money' => 'Mobile Money',
            'virement' => 'Virement',
            'carte' => 'Carte bancaire',
            'especes' => 'Espèces',
            'cheque' => 'Chèque',
            'autre' => 'Autre',
            default => ucfirst(str_replace('_', ' ', $mode)),
        };
    }

    private function formatPaymentDetail(?Transaction $transaction): string
    {
        if (!$transaction) {
            return 'Non renseigné';
        }

        $notes = trim((string) ($transaction->notes ?? ''));
        $referencePaiement = trim((string) ($transaction->reference_paiement ?? ''));
        $mode = (string) ($transaction->mode_paiement ?? '');

        if ($mode === 'mobile_money' && preg_match('/(\+?\d[\d\s().-]{6,}\d)/', $notes, $matches)) {
            return 'Téléphone: ' . trim($matches[1]);
        }

        if ($mode === 'carte' && preg_match('/(\d{4})\D*$/', $referencePaiement, $matches)) {
            return 'Carte •••• ' . $matches[1];
        }

        if ($notes !== '' && !Str::startsWith(Str::lower($notes), 'validation test')) {
            return Str::limit($notes, 80);
        }

        if ($referencePaiement !== '') {
            return $referencePaiement;
        }

        return 'Non renseigné';
    }

    private function buildReceiptPdf(Transaction $transaction, string $recipientName): string
    {
        $pdf = new \FPDF('P', 'mm', 'A4');
        $pdf->SetAutoPageBreak(true, 16);
        $pdf->AddPage();
        $pdf->SetMargins(15, 15, 15);

        $pdf->SetFont('Arial', 'B', 16);
        $pdf->Cell(0, 10, $this->pdfText(company_name()), 0, 1, 'C');
        $pdf->SetFont('Arial', '', 11);
        $pdf->Cell(0, 7, $this->pdfText('Reçu de paiement d\'abonnement'), 0, 1, 'C');
        $pdf->Ln(6);

        $this->pdfSection($pdf, 'Agence', $transaction->agence?->name ?? '—');
        $this->pdfSection($pdf, 'Destinataire', $recipientName);
        $this->pdfSection($pdf, 'Référence', $transaction->reference ?? '—');
        $this->pdfSection($pdf, 'Statut', $this->formatPaymentStatusLabel($transaction->statut));
        $this->pdfSection($pdf, 'Montant', number_format((float) ($transaction->montant_ttc ?? 0), 0, ',', ' ') . ' FCFA');
        $this->pdfSection($pdf, 'Période', $this->formatPeriodLabel($transaction->periode_debut, $transaction->periode_fin));
        $this->pdfSection($pdf, 'Mode de paiement', $this->formatPaymentModeLabel($transaction->mode_paiement));
        $this->pdfSection($pdf, 'Détail', $this->formatPaymentDetail($transaction));
        $this->pdfSection($pdf, 'Date de paiement', $this->formatDateTimeForClient($transaction->date_paiement) ?? '—');
        $this->pdfSection($pdf, 'Référence paiement', $transaction->reference_paiement ?? '—');

        $pdf->Ln(8);
        $pdf->SetFont('Arial', '', 10);
        $pdf->MultiCell(0, 6, $this->pdfText('Pour toute contestation ou précision, contactez le support via ' . (company_phone() ?: 'le support de votre agence') . ' ' . (company_email() ? 'ou ' . company_email() : '') . '.'), 0, 'L');

        return $pdf->Output('S');
    }

    private function pdfSection(\FPDF $pdf, string $label, string $value): void
    {
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->Cell(55, 8, $this->pdfText($label), 1, 0, 'L');
        $pdf->SetFont('Arial', '', 11);
        $pdf->MultiCell(0, 8, $this->pdfText($value), 1, 'L');
    }

    private function pdfText(string $text): string
    {
        return iconv('UTF-8', 'windows-1252//TRANSLIT', $text) ?: $text;
    }

    private function formatPaymentStatusLabel(?string $status): string
    {
        return match ($status) {
            'validee' => 'Validée',
            'en_attente' => 'En attente',
            'echouee' => 'Échouée',
            'annulee' => 'Annulée',
            'remboursee' => 'Remboursée',
            default => ucfirst((string) $status),
        };
    }

    private function extractSubscriptionModules($subscription): array
    {
        $featureModules = collect($subscription?->features ?? [])
            ->map(function ($feature) {
                if (is_string($feature)) {
                    return $feature;
                }

                if (!is_array($feature)) {
                    return null;
                }

                return $feature['label'] ?? $feature['nom'] ?? $feature['name'] ?? null;
            })
            ->filter()
            ->values()
            ->all();

        if (!empty($featureModules)) {
            return $featureModules;
        }

        return array_values(array_filter([
            $subscription?->module_comptabilite ? 'Comptabilité' : null,
            $subscription?->module_reporting ? 'Reporting' : null,
            $subscription?->module_api ? 'API' : null,
        ]));
    }
}
