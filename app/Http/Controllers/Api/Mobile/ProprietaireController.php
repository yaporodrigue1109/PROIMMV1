<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Agence;
use App\Models\LocataireAgence;
use App\Models\Loyer;
use App\Models\ProprietaireAgence;
use App\Models\Propriete;
use App\Models\TransactionAgence;
use App\Services\Mobile\AgencyPortalAccessService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProprietaireController extends Controller
{
    public function __construct(private readonly AgencyPortalAccessService $portalAccess) {}

    public function agencies(Request $request): JsonResponse
    {
        $ownerId = $request->attributes->get('mobile_actor')->getKey();
        $links = ProprietaireAgence::with(['agence.ville', 'agence.region'])
            ->where('proprietaire_id', $ownerId)
            ->where('is_mobile_visible', true)
            ->active()->get()
            ->filter(fn ($link) => $link->agence && $this->portalAccess->enabled($link->agence, 'proprietaire'));

        return response()->json(['data' => $links->map(fn ($link) => $this->agencyData($link->agence))->values()]);
    }

    public function attachAgency(Request $request): JsonResponse
    {
        $data = $request->validate(['code' => ['required', 'string', 'max:150']]);
        $ownerId = $request->attributes->get('mobile_actor')->getKey();
        $agency = Agence::where('code_agence', trim($data['code']))->first();

        if (! $agency) {
            throw ValidationException::withMessages(['code' => "Ce code d'agence est invalide."]);
        }

        if (! $this->portalAccess->enabled($agency, 'proprietaire')) {
            throw ValidationException::withMessages([
                'code' => "Le portail propriétaire n'est pas actif dans l'abonnement de cette agence.",
            ]);
        }

        $link = ProprietaireAgence::where('proprietaire_id', $ownerId)
            ->where('agence_id', $agency->getKey())->active()->first();
        if (! $link) {
            throw ValidationException::withMessages([
                'code' => "Aucun mandat actif ne relie ce compte à l'agence.",
            ]);
        }

        $alreadyVisible = $link->is_mobile_visible;
        if (! $alreadyVisible) {
            DB::table('proprietaire_agences')
                ->where('proprietaire_agence_id', $link->getKey())
                ->update(['is_mobile_visible' => true]);
        }

        return response()->json([
            'message' => $alreadyVisible ? 'Cette agence est déjà ajoutée.' : 'Agence ajoutée avec succès.',
            'data' => $this->agencyData($agency),
        ]);
    }

    public function dashboard(Request $request, string $agency): JsonResponse
    {
        $ownerId = $request->attributes->get('mobile_actor')->getKey();
        $this->assertAgencyAccess($ownerId, $agency);
        $data = $request->validate([
            'year' => ['nullable', 'integer', 'between:2000,2100'],
            'month' => ['nullable', 'integer', 'between:1,12'],
        ]);
        $year = $data['year'] ?? now()->year;
        $month = $data['month'] ?? null;

        $loyers = Loyer::where('proprietaire_id', $ownerId)->where('agence_id', $agency)
            ->where('annee_paiement', $year)
            ->when($month, fn ($q, $value) => $q->where('mois_paiement', $value));

        $monthly = Loyer::query()
            ->selectRaw('mois_paiement as month, SUM(montant_proprio) as amount')
            ->where('proprietaire_id', $ownerId)->where('agence_id', $agency)
            ->where('annee_paiement', $year)->groupBy('mois_paiement')
            ->orderBy('mois_paiement')->get()
            ->map(fn ($row) => ['month' => (int) $row->month, 'amount' => (int) $row->amount]);

        return response()->json(['data' => [
            'period' => ['year' => (int) $year, 'month' => $month ? (int) $month : null],
            'summary' => [
                'expected' => (int) (clone $loyers)->sum('montant_a_payer'),
                'received' => (int) (clone $loyers)->sum('montant_payer'),
                'owner_share' => (int) (clone $loyers)->sum('montant_proprio'),
                'arrears' => (int) (clone $loyers)->sum('montant_restant'),
                'properties' => Propriete::where('proprietaire_id', $ownerId)->where('agence_id', $agency)->count(),
            ],
            'monthly_received' => $monthly,
        ]]);
    }

    public function properties(Request $request, string $agency): JsonResponse
    {
        $ownerId = $request->attributes->get('mobile_actor')->getKey();
        $this->assertAgencyAccess($ownerId, $agency);

        $properties = Propriete::with(['typePropriete', 'lot', 'batiments.portes'])
            ->where('proprietaire_id', $ownerId)->where('agence_id', $agency)
            ->orderBy('reference')->get();

        return response()->json(['data' => $properties->map(fn ($property) => $this->propertyData($property, $ownerId, $agency))->values()]);
    }

    public function property(Request $request, string $agency, string $property): JsonResponse
    {
        $ownerId = $request->attributes->get('mobile_actor')->getKey();
        $item = $this->ownedProperty($ownerId, $agency, $property);

        $rent = Loyer::where('proprietaire_id', $ownerId)->where('agence_id', $agency)->where('propriete_id', $property);
        $lastPayout = TransactionAgence::where('proprietaire_id', $ownerId)
            ->where('agence_id', $agency)->where('propriete_id', $property)
            ->where('is_reversement', true)->orderByDesc('date_transaction')->first();

        return response()->json(['data' => $this->propertyData($item, $ownerId, $agency) + [
            'expected' => (int) (clone $rent)->sum('montant_a_payer'),
            'received' => (int) (clone $rent)->sum('montant_payer'),
            'arrears' => (int) (clone $rent)->sum('montant_restant'),
            'last_payout' => $lastPayout ? $this->payoutData($lastPayout) : null,
        ]]);
    }

    public function tenants(Request $request, string $agency, string $property): JsonResponse
    {
        $ownerId = $request->attributes->get('mobile_actor')->getKey();
        $this->ownedProperty($ownerId, $agency, $property);
        $contracts = LocataireAgence::with(['locataire', 'batiment', 'porte'])
            ->where('proprietaire_id', $ownerId)->where('agence_id', $agency)
            ->where('propriete_id', $property)->where('is_active', true)->get();

        return response()->json(['data' => $contracts->map(function ($contract) use ($agency, $property) {
            $arrears = Loyer::where('locataire_id', $contract->locataire_id)
                ->where('agence_id', $agency)->where('propriete_id', $property)->sum('montant_restant');

            return [
                'contract_id' => $contract->getKey(),
                'tenant_id' => $contract->locataire_id,
                'name' => $contract->locataire?->name,
                'phone' => $contract->locataire?->tel1,
                'photo_url' => $contract->locataire?->photo ? url($contract->locataire->photo) : null,
                'building' => $contract->batiment?->name,
                'door' => $contract->porte?->numero_porte,
                'monthly_rent' => (int) ($contract->loyer_net ?: $contract->porte?->mt_loyer),
                'arrears' => (int) $arrears,
                'lease_started_at' => optional($contract->date_debut_bail)->toDateString(),
            ];
        })->values()]);
    }

    public function arrears(Request $request, string $agency, string $property): JsonResponse
    {
        $response = $this->tenants($request, $agency, $property);
        $payload = $response->getData(true);
        $payload['data'] = collect($payload['data'])->where('arrears', '>', 0)->values()->all();
        $payload['total'] = collect($payload['data'])->sum('arrears');

        return response()->json($payload);
    }

    public function payouts(Request $request, string $agency, string $property): JsonResponse
    {
        $ownerId = $request->attributes->get('mobile_actor')->getKey();
        $this->ownedProperty($ownerId, $agency, $property);
        $data = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
            'per_page' => ['nullable', 'integer', 'between:1,100'],
        ]);

        $page = TransactionAgence::with('modePaiement')
            ->where('proprietaire_id', $ownerId)->where('agence_id', $agency)
            ->where('propriete_id', $property)->where('is_reversement', true)
            ->when($data['from'] ?? null, fn ($q, $date) => $q->whereDate('date_transaction', '>=', $date))
            ->when($data['to'] ?? null, fn ($q, $date) => $q->whereDate('date_transaction', '<=', $date))
            ->orderByDesc('date_transaction')->paginate($data['per_page'] ?? 20);
        $page->through(fn ($transaction) => $this->payoutData($transaction));

        return response()->json($page);
    }

    private function propertyData(Propriete $property, string $ownerId, string $agencyId): array
    {
        $doors = $property->batiments->flatMap->portes;
        $rent = Loyer::where('proprietaire_id', $ownerId)->where('agence_id', $agencyId)->where('propriete_id', $property->getKey());

        return [
            'id' => $property->getKey(),
            'title' => $property->reference,
            'description' => $property->description,
            'type' => $property->typePropriete?->name,
            'location' => $property->adresse_complete ?: $property->lot?->adresse,
            'is_active' => (bool) $property->is_actif,
            'buildings' => $property->batiments->count(),
            'doors' => $doors->count(),
            'occupied_doors' => $doors->where('is_occupe', true)->count(),
            'tenants' => LocataireAgence::where('proprietaire_id', $ownerId)->where('agence_id', $agencyId)
                ->where('propriete_id', $property->getKey())->where('is_active', true)->count(),
            'expected' => (int) (clone $rent)->sum('montant_a_payer'),
            'received' => (int) (clone $rent)->sum('montant_payer'),
        ];
    }

    private function payoutData(TransactionAgence $transaction): array
    {
        return [
            'id' => $transaction->getKey(),
            'reference' => 'REV-'.strtoupper(substr($transaction->getKey(), 0, 8)),
            'amount' => (int) $transaction->montant_global_verser,
            'payment_method' => $transaction->modePaiement?->name,
            'date' => optional($transaction->date_transaction)->toIso8601String(),
        ];
    }

    private function assertAgencyAccess(string $ownerId, string $agencyId): void
    {
        $hasVisibleMandate = ProprietaireAgence::where('proprietaire_id', $ownerId)
            ->where('agence_id', $agencyId)
            ->where('is_mobile_visible', true)
            ->active()->exists();

        abort_unless(
            $hasVisibleMandate && $this->portalAccess->enabled($agencyId, 'proprietaire'),
            404
        );
    }

    private function ownedProperty(string $ownerId, string $agencyId, string $propertyId): Propriete
    {
        $this->assertAgencyAccess($ownerId, $agencyId);

        return Propriete::with(['typePropriete', 'lot', 'batiments.portes'])
            ->where('proprietaire_id', $ownerId)->where('agence_id', $agencyId)->findOrFail($propertyId);
    }

    private function agencyData(?Agence $agency): array
    {
        abort_if(! $agency, 404);

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
        ];
    }
}
