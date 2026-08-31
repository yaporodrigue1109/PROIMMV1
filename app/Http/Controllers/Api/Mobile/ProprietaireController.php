<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Agence;
use App\Models\LocataireAgence;
use App\Models\Loyer;
use App\Models\Maintenance;
use App\Models\Porte;
use App\Models\ProprietaireAgence;
use App\Models\Propriete;
use App\Models\Reversement;
use App\Services\Mobile\AgencyPortalAccessService;
use App\Services\Agence\ReversementPdfService;
use App\Services\Agence\AgencyDocumentBranding;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\ValidationException;

class ProprietaireController extends Controller
{
    public function __construct(
        private readonly AgencyPortalAccessService $portalAccess,
        private readonly ReversementPdfService $pdfService,
        private readonly AgencyDocumentBranding $documentBranding,
    ) {}

    public function agencies(Request $request): JsonResponse
    {
        $ownerId = $request->attributes->get('mobile_actor')->getKey();
        $links = ProprietaireAgence::with(['agence.ville', 'agence.region', 'agence.parametrage'])
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

        $monthlyReversements = Reversement::query()
            ->where('proprietaire_id', $ownerId)
            ->where('agence_id', $agency)
            ->reverse()
            ->whereNotNull('date_reversement')
            ->whereYear('date_reversement', $year)
            ->get(['date_reversement', 'net_a_reverser', 'total_restant'])
            ->groupBy(fn ($reversement) => (int) $reversement->date_reversement->format('n'));

        $monthlyPayouts = $monthlyReversements
            ->map(fn ($reversements, $month) => [
                'month' => (int) $month,
                'amount' => (int) $reversements->sum('net_a_reverser'),
            ])
            ->sortBy('month')
            ->values();

        $monthlyArrears = $monthlyReversements
            ->map(fn ($reversements, $month) => [
                'month' => (int) $month,
                'amount' => (int) $reversements->sum('total_restant'),
            ])
            ->sortBy('month')
            ->values();

        $expected = Porte::query()
            ->where('agence_id', $agency)
            ->where('is_occupe', true)
            ->whereHas('locatairesAgence', function ($query) use ($ownerId, $agency) {
                $query->where('proprietaire_id', $ownerId)
                    ->where('agence_id', $agency)
                    ->where('is_active', true);
            })
            ->sum('mt_loyer');

        $arrears = Loyer::query()
            ->where('proprietaire_id', $ownerId)
            ->where('agence_id', $agency)
            ->impayesOuPartiels()
            ->sum('montant_restant');

        return response()->json(['data' => [
            'period' => ['year' => (int) $year, 'month' => $month ? (int) $month : null],
            'summary' => [
                'expected' => (int) $expected,
                'received' => (int) (clone $loyers)->sum('montant_payer'),
                'owner_share' => (int) (clone $loyers)->sum('montant_proprio'),
                'arrears' => (int) $arrears,
                'properties' => Propriete::where('proprietaire_id', $ownerId)->where('agence_id', $agency)->count(),
                'tenants' => LocataireAgence::where('proprietaire_id', $ownerId)
                    ->where('agence_id', $agency)
                    ->where('is_active', true)
                    ->distinct('locataire_id')
                    ->count('locataire_id'),
            ],
            'monthly_payouts' => $monthlyPayouts,
            'monthly_arrears' => $monthlyArrears,
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
        $lastPayout = Reversement::where('proprietaire_id', $ownerId)
            ->where('agence_id', $agency)
            ->where('lot_id', $item->lot_id)
            ->reverse()
            ->orderByDesc('date_reversement')
            ->first();

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
                'photo_url' => $contract->locataire?->photo
                    ? request()->root().'/'.ltrim(parse_url($contract->locataire->photo, PHP_URL_PATH) ?: $contract->locataire->photo, '/')
                    : null,
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
        $ownedProperty = $this->ownedProperty($ownerId, $agency, $property);
        $data = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
            'per_page' => ['nullable', 'integer', 'between:1,100'],
        ]);

        $page = Reversement::query()
            ->where('proprietaire_id', $ownerId)
            ->where('agence_id', $agency)
            ->where('lot_id', $ownedProperty->lot_id)
            ->reverse()
            ->when($data['from'] ?? null, fn ($q, $date) => $q->whereDate('date_reversement', '>=', $date))
            ->when($data['to'] ?? null, fn ($q, $date) => $q->whereDate('date_reversement', '<=', $date))
            ->orderByDesc('date_reversement')
            ->paginate($data['per_page'] ?? 10);
        $page->through(fn ($reversement) => $this->payoutData($reversement));

        return response()->json($page);
    }

    public function maintenances(Request $request, string $agency, string $property): JsonResponse
    {
        $ownerId = $request->attributes->get('mobile_actor')->getKey();
        $ownedProperty = $this->ownedProperty($ownerId, $agency, $property);
        $data = $request->validate([
            'per_page' => ['nullable', 'integer', 'between:1,100'],
        ]);

        $page = Maintenance::with([
            'batiment',
            'porte',
            'details.typeIntervention',
            'details.maintenancier',
        ])
            ->where('proprietaire_id', $ownerId)
            ->where('agence_id', $agency)
            ->where('lot_id', $ownedProperty->lot_id)
            ->orderByDesc('created_at')
            ->paginate($data['per_page'] ?? 10);

        $page->through(fn (Maintenance $maintenance) => [
            'id' => $maintenance->getKey(),
            'title' => $maintenance->titre,
            'description' => $maintenance->description,
            'status' => $maintenance->statut,
            'amount' => (int) $maintenance->montant_global,
            'covered_by' => $maintenance->prise_en_charge_par,
            'building' => $maintenance->batiment?->name,
            'door' => $maintenance->porte?->numero_porte,
            'created_at' => optional($maintenance->created_at)->toIso8601String(),
            'updated_at' => optional($maintenance->updated_at)->toIso8601String(),
            'details' => $maintenance->details->map(fn ($detail) => [
                'id' => $detail->getKey(),
                'type' => $detail->typeIntervention?->name,
                'technician' => $detail->maintenancier?->name,
                'technician_phone' => $detail->maintenancier?->tel1,
                'status' => $detail->statut,
                'priority' => $detail->priorite,
                'amount' => (int) $detail->montant,
                'start_date' => optional($detail->date_debut)->toDateString(),
                'end_date' => optional($detail->date_fin)->toDateString(),
                'note' => $detail->note,
            ])->values(),
        ]);

        return response()->json($page);
    }

    public function downloadPayout(Request $request, Reversement $reversement)
    {
        abort_unless($request->hasValidSignature(false), 403);
        abort_unless(
            hash_equals((string) $reversement->proprietaire_id, (string) $request->query('owner'))
                && $reversement->statut === 'reverse',
            404
        );

        $reversement->loadMissing([
            'agence.parametrage',
            'proprietaire',
            'lot',
            'details.locataire',
            'details.porte',
        ]);

        $documentLogo = $this->documentBranding->logoUrl($reversement->agence);

        return view('agence.reversement.mobile', compact('reversement', 'documentLogo'));
    }

    private function propertyData(Propriete $property, string $ownerId, string $agencyId): array
    {
        $doors = $property->batiments->flatMap->portes;
        $rent = Loyer::where('proprietaire_id', $ownerId)->where('agence_id', $agencyId)->where('propriete_id', $property->getKey());
        $lot = $property->lot;
        $lotDetails = collect([
            filled($lot?->num_ilot) ? 'Îlot '.$lot->num_ilot : null,
            filled($lot?->num_lot) ? 'Lot '.$lot->num_lot : null,
        ])->filter()->implode(' • ');
        $title = collect([
            filled($lot?->name) ? $lot->name : 'Lot',
            $lotDetails ?: null,
        ])->filter()->implode(' — ');

        return [
            'id' => $property->getKey(),
            'title' => $title,
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

    private function payoutData(Reversement $reversement): array
    {
        return [
            'id' => $reversement->getKey(),
            'reference' => $reversement->reference_paiement,
            'amount' => (int) $reversement->net_a_reverser,
            'payment_method' => $reversement->mode_paiement,
            'date' => optional($reversement->date_reversement)->toIso8601String(),
            'download_url' => URL::temporarySignedRoute(
                'mobile.proprietaire.reversements.pdf',
                now()->addHours(2),
                ['reversement' => $reversement->getKey(), 'owner' => $reversement->proprietaire_id],
                false
            ),
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
}
