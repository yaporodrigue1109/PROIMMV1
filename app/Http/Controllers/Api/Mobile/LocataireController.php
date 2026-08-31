<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\WebController;
use App\Models\Agence;
use App\Models\LocataireAgence;
use App\Models\Loyer;
use App\Models\Maintenance;
use App\Models\AgencyAnnouncementRecipient;
use App\Models\TransactionAgence;
use App\Services\Agence\AgencyDocumentBranding;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LocataireController extends Controller
{
    public function __construct(
        private readonly WebController $portal,
        private readonly AgencyDocumentBranding $documentBranding,
    ) {
    }

    public function agencies(Request $request): JsonResponse
    {
        $locataire = $request->attributes->get('mobile_actor');
        $contracts = LocataireAgence::query()
            ->with(['agency.ville', 'agency.region', 'agency.parametrage'])
            ->where('locataire_id', $locataire->getKey())
            ->where('is_active', true)
            ->whereIn('agence_id', $this->portal->tenantPortalAgencyIds())
            ->whereExists(function ($query) {
                $query->selectRaw('1')
                    ->from('locataire_agence as mobile_access')
                    ->whereColumn('mobile_access.locataire_id', 'locataire_agence.locataire_id')
                    ->whereColumn('mobile_access.agence_id', 'locataire_agence.agence_id')
                    ->where('mobile_access.is_mobile_visible', true);
            })
            ->get()
            ->unique('agence_id');

        return response()->json([
            'data' => $contracts
                ->map(fn ($contract) => $this->agencyData($contract->agency))
                ->values(),
        ]);
    }

    public function attachAgency(Request $request): JsonResponse
    {
        $data = $request->validate(['code' => ['required', 'string', 'max:150']]);
        $locataire = $request->attributes->get('mobile_actor');
        $agency = Agence::where('code_agence', trim($data['code']))->first();

        if (! $agency) {
            throw ValidationException::withMessages(['code' => "Ce code d'agence est invalide."]);
        }

        if (! $this->portal->isTenantPortalAgencyEligible($agency->getKey())) {
            throw ValidationException::withMessages([
                'code' => "Cette agence n'est pas disponible actuellement sur l'application mobile.",
            ]);
        }


        $contracts = LocataireAgence::where('locataire_id', $locataire->getKey())
            ->where('agence_id', $agency->getKey())
            ->where('is_active', true)
            ->get();

        if ($contracts->isEmpty()) {
            throw ValidationException::withMessages([
                'code' => "Aucun bail actif ne relie ce compte à l'agence.",
            ]);
        }

        $alreadyVisible = $contracts->every(fn ($contract) => $contract->is_mobile_visible);
        if (! $alreadyVisible) {
            DB::table('locataire_agence')
                ->where('locataire_id', $locataire->getKey())
                ->where('agence_id', $agency->getKey())
                ->update(['is_mobile_visible' => true]);
        }

        return response()->json([
            'message' => $alreadyVisible ? 'Cette agence est déjà ajoutée.' : 'Agence ajoutée avec succès.',
            'data' => $this->agencyData($agency),
        ]);
    }

    public function agency(Request $request, string $agency): JsonResponse
    {
        $locataireId = $request->attributes->get('mobile_actor')->getKey();
        $this->assertAccess($locataireId, $agency);
        $agence = Agence::with(['ville', 'region'])->findOrFail($agency);

        $loyers = Loyer::where('locataire_id', $locataireId)->where('agence_id', $agency);

        return response()->json(['data' => [
            'agency' => $this->agencyData($agence),
            'summary' => [
                'expected' => (int) (clone $loyers)->sum('montant_a_payer'),
                'paid' => (int) (clone $loyers)->sum('montant_payer'),
                'arrears' => (int) (clone $loyers)->sum('montant_restant'),
            ],
        ]]);
    }

    public function properties(Request $request, string $agency): JsonResponse
    {
        $locataireId = $request->attributes->get('mobile_actor')->getKey();
        $this->assertAccess($locataireId, $agency);

        $contracts = LocataireAgence::with(['propriete.typePropriete', 'batiment', 'porte'])
            ->where('locataire_id', $locataireId)
            ->where('agence_id', $agency)
            ->where('is_active', true)
            ->get();

        return response()->json(['data' => $contracts->map(function ($contract) use ($locataireId, $agency) {
            $loyers = Loyer::where('locataire_id', $locataireId)
                ->where('agence_id', $agency)
                ->where('porte_id', $contract->porte_id);
            $lastPayment = TransactionAgence::where('locataire_id', $locataireId)
                ->where('agence_id', $agency)
                ->where('porte_id', $contract->porte_id)
                ->where('type_transaction', 'loyer')
                ->latest('date_transaction')
                ->value('montant_global_verser');

            return [
                'contract_id' => $contract->getKey(),
                'property_id' => $contract->propriete_id,
                'title' => $contract->propriete?->description
                    ?: ($contract->propriete?->typePropriete?->name ?: 'Bien immobilier'),
                'type' => $contract->propriete?->typePropriete?->name,
                'location' => $contract->propriete?->adresse_complete,
                'building' => $contract->batiment?->name,
                'door_id' => $contract->porte_id,
                'door' => $contract->porte?->numero_porte,
                'monthly_rent' => (int) ($contract->loyer_net ?: $contract->porte?->mt_loyer),
                'lease_started_at' => optional($contract->date_debut_bail)->toDateString(),
                'last_payment' => (int) ($lastPayment ?? 0),
                'arrears' => (int) (clone $loyers)->sum('montant_restant'),
            ];
        })->values()]);
    }

    public function receipts(Request $request, string $agency): JsonResponse
    {
        $locataireId = $request->attributes->get('mobile_actor')->getKey();
        $this->assertAccess($locataireId, $agency);
        $data = $request->validate([
            'door_id' => ['nullable', 'string'],
            'month' => ['nullable', 'date_format:Y-m'],
            'per_page' => ['nullable', 'integer', 'between:1,100'],
        ]);

        $query = TransactionAgence::with(['propriete', 'porte.batiment.propriete', 'modePaiement'])
            ->where('locataire_id', $locataireId)
            ->where('agence_id', $agency)
            ->where('type_transaction', 'loyer')
            ->when($data['door_id'] ?? null, fn ($q, $id) => $q->where('porte_id', $id))
            ->when($data['month'] ?? null, function ($query, $month) {
                [$year, $monthNumber] = explode('-', $month);

                $query->whereYear('date_transaction', $year)
                    ->whereMonth('date_transaction', $monthNumber);
            })
            ->orderByDesc('date_transaction')
            ->orderByDesc('created_at');

        $page = $query->paginate($data['per_page'] ?? 10);
        $page->through(fn ($transaction) => [
            'id' => $transaction->getKey(),
            'reference' => $transaction->numero_recu
                ?: ($transaction->reference ?: 'REC-'.strtoupper(substr($transaction->getKey(), 0, 8))),
            'property_id' => $transaction->propriete_id,
            'property' => $transaction->propriete?->reference
                ?: $transaction->porte?->batiment?->propriete?->reference,
            'door' => $transaction->porte?->numero_porte,
            'amount' => (int) $transaction->montant_global_verser,
            'rent_amount' => (int) $transaction->montant_loyer_payer,
            'arrears_amount' => (int) $transaction->montant_arriere_payer,
            'current_arrears' => (int) $transaction->montant_arriere_actuel,
            'advance_amount' => (int) $transaction->montant_avance_payer,
            'paid_periods' => $this->decodePeriods($transaction->mois_payer),
            'payment_method' => $transaction->modePaiement?->name,
            'date' => optional($transaction->date_transaction)->toIso8601String(),
        ]);

        return response()->json($page);
    }

    public function arrears(Request $request, string $agency): JsonResponse
    {
        $locataireId = $request->attributes->get('mobile_actor')->getKey();
        $this->assertAccess($locataireId, $agency);
        $data = $request->validate([
            'door_id' => ['nullable', 'string'],
            'month' => ['nullable', 'date_format:Y-m'],
            'per_page' => ['nullable', 'integer', 'between:1,100'],
        ]);

        $query = Loyer::with('porte')
            ->where('locataire_id', $locataireId)
            ->where('agence_id', $agency)
            ->where('montant_restant', '>', 0)
            ->when($data['door_id'] ?? null, fn ($q, $id) => $q->where('porte_id', $id))
            ->when($data['month'] ?? null, function ($query, $month) {
                [$year, $monthNumber] = explode('-', $month);

                $query->where('annee_paiement', $year)
                    ->where('mois_paiement', $monthNumber);
            })
            ->orderByDesc('annee_paiement')
            ->orderByDesc('mois_paiement')
            ->orderByDesc('created_at');

        $page = $query->paginate($data['per_page'] ?? 10);
        $page->through(fn ($rent) => [
            'id' => $rent->getKey(),
            'door_id' => $rent->porte_id,
            'door' => $rent->porte?->numero_porte,
            'month' => (int) $rent->mois_paiement,
            'year' => (int) $rent->annee_paiement,
            'remaining_amount' => (int) $rent->montant_restant,
            'due_date' => optional($rent->date_limit_paiement)->toDateString(),
            'status' => $rent->statut,
        ]);

        return response()->json($page);
    }

    public function maintenances(Request $request, string $agency): JsonResponse
    {
        $locataireId = $request->attributes->get('mobile_actor')->getKey();
        $this->assertAccess($locataireId, $agency);
        $data = $request->validate([
            'door_id' => ['nullable', 'string'],
            'per_page' => ['nullable', 'integer', 'between:1,100'],
        ]);

        $items = Maintenance::with(['porte', 'details.typeIntervention', 'details.maintenancier'])
            ->where('agence_id', $agency)
            ->where('locataire_id', $locataireId)
            ->when($data['door_id'] ?? null, fn ($query, $doorId) => $query->where('porte_id', $doorId))
            ->orderByDesc('created_at')
            ->orderByDesc('updated_at')
            ->paginate($data['per_page'] ?? 10);

        $items->through(fn ($item) => $this->maintenanceData($item));

        return response()->json($items);
    }

    public function storeMaintenance(Request $request, string $agency): JsonResponse
    {
        $locataireId = $request->attributes->get('mobile_actor')->getKey();
        $this->assertAccess($locataireId, $agency);
        $data = $request->validate([
            'door_id' => ['required', 'string'],
            'title' => ['required', 'string', 'max:200'],
            'description' => ['required', 'string', 'max:3000'],
        ]);

        $contract = LocataireAgence::where('locataire_id', $locataireId)
            ->where('agence_id', $agency)
            ->where('porte_id', $data['door_id'])
            ->where('is_active', true)
            ->first();

        if (! $contract) {
            throw ValidationException::withMessages([
                'door_id' => "Cette porte n'est liée à aucun de vos baux actifs.",
            ]);
        }

        $maintenance = Maintenance::create([
            'agence_id' => $agency,
            'locataire_id' => $locataireId,
            'proprietaire_id' => $contract->proprietaire_id,
            'lot_id' => $contract->lot_id,
            'propriete_id' => $contract->propriete_id,
            'batiment_id' => $contract->batiment_id,
            'porte_id' => $contract->porte_id,
            'titre' => trim($data['title']),
            'description' => trim($data['description']),
            'statut' => Maintenance::STATUT_EN_ATTENTE,
            'montant_global' => 0,
        ]);

        return response()->json([
            'message' => 'Demande de maintenance envoyée avec succès.',
            'data' => $this->maintenanceData($maintenance->load('porte')),
        ], 201);
    }

    private function maintenanceData(Maintenance $maintenance): array
    {
        return [
            'id' => $maintenance->getKey(),
            'door_id' => $maintenance->porte_id,
            'door' => $maintenance->porte?->numero_porte,
            'title' => $maintenance->titre,
            'description' => $maintenance->description,
            'status' => $maintenance->statut,
            'created_at' => optional($maintenance->created_at)->toIso8601String(),
            'updated_at' => optional($maintenance->updated_at)->toIso8601String(),
            'steps' => $maintenance->details->map(fn ($detail) => [
                'id' => $detail->getKey(),
                'type' => $detail->typeIntervention?->name,
                'technician' => $detail->maintenancier?->name,
                'status' => $detail->statut,
                'priority' => $detail->priorite,
                'start_date' => optional($detail->date_debut)->toDateString(),
                'end_date' => optional($detail->date_fin)->toDateString(),
                'note' => $detail->note,
            ])->values(),
        ];
    }

    public function announcements(Request $request, string $agency): JsonResponse
    {
        $locataireId = $request->attributes->get('mobile_actor')->getKey();
        $this->assertAccess($locataireId, $agency);
        $data = $request->validate([
            'per_page' => ['nullable', 'integer', 'between:1,100'],
        ]);
        $query = AgencyAnnouncementRecipient::with('announcement')
            ->where('locataire_id', $locataireId)
            ->whereHas('announcement', fn ($q) => $q->where('agence_id', $agency)->whereNotNull('published_at'))
            ->orderByDesc(
                \App\Models\AgencyAnnouncement::select('published_at')
                    ->whereColumn('agency_announcements.announcement_id', 'agency_announcement_recipients.announcement_id')
            );
        $unreadCount = (clone $query)->whereNull('read_at')->count();
        $items = $query->paginate($data['per_page'] ?? 10);
        $items->through(fn ($item) => [
            'recipient_id' => $item->getKey(),
            'id' => $item->announcement->getKey(),
            'title' => $item->announcement->title,
            'message' => $item->announcement->message,
            'published_at' => optional($item->announcement->published_at)->toIso8601String(),
            'read_at' => optional($item->read_at)->toIso8601String(),
        ]);

        return response()->json(array_merge($items->toArray(), [
            'unread_count' => $unreadCount,
        ]));
    }

    public function readAnnouncement(Request $request, string $agency, string $recipient): JsonResponse
    {
        $locataireId = $request->attributes->get('mobile_actor')->getKey();
        $this->assertAccess($locataireId, $agency);
        $item = AgencyAnnouncementRecipient::whereKey($recipient)
            ->where('locataire_id', $locataireId)
            ->whereHas('announcement', fn ($q) => $q->where('agence_id', $agency))
            ->firstOrFail();
        $item->forceFill(['read_at' => $item->read_at ?? now()])->save();

        return response()->json(['message' => 'Annonce marquée comme lue.']);
    }

    private function assertAccess(string $locataireId, string $agencyId): void
    {
        abort_unless(
            $this->portal->isTenantPortalAgencyEligible($agencyId),
            403,
            "Cette agence n'est pas disponible actuellement sur l'application mobile."
        );

        $hasActiveLease = LocataireAgence::where('locataire_id', $locataireId)
            ->where('agence_id', $agencyId)
            ->where('is_active', true)
            ->exists();
        $codeWasValidated = LocataireAgence::where('locataire_id', $locataireId)
            ->where('agence_id', $agencyId)
            ->where('is_mobile_visible', true)
            ->exists();

        abort_unless($hasActiveLease && $codeWasValidated, 404);
    }

    private function agencyData(?Agence $agency): array
    {
        abort_if(! $agency, 404);
        $agency->loadMissing('parametrage');
        $hasConfiguredLogo = filled($agency->parametrage?->logo);

        return [
            'id' => $agency->getKey(),
            'code' => trim((string) $agency->code_agence),
            'name' => $agency->name,
            'email' => $agency->email1,
            'phone' => $agency->tel1,
            'address' => $agency->adresse,
            'city' => $agency->ville?->name,
            'region' => $agency->region?->name,
            'website' => $agency->site_web,
            'status' => $agency->statut,
            'logo_url' => $hasConfiguredLogo
                ? $this->documentBranding->logoUrl($agency)
                : null,
        ];
    }

    private function decodePeriods(?string $periods): array
    {
        $decoded = json_decode($periods ?: '[]', true);

        return is_array($decoded) ? $decoded : [];
    }
}
