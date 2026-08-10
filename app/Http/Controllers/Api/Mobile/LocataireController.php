<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Agence;
use App\Models\LocataireAgence;
use App\Models\Loyer;
use App\Models\TransactionAgence;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LocataireController extends Controller
{
    public function agencies(Request $request): JsonResponse
    {
        $locataire = $request->attributes->get('mobile_actor');
        $contracts = LocataireAgence::query()
            ->with(['agence.ville', 'agence.region'])
            ->where('locataire_id', $locataire->getKey())
            ->where('is_active', true)
            ->whereExists(function ($query) {
                $query->selectRaw('1')
                    ->from('locataire_agence as mobile_access')
                    ->whereColumn('mobile_access.locataire_id', 'locataire_agence.locataire_id')
                    ->whereColumn('mobile_access.agence_id', 'locataire_agence.agence_id')
                    ->where('mobile_access.is_mobile_visible', true);
            })
            ->get()
            ->unique('agence_id');

        return response()->json(['data' => $contracts->map(fn ($contract) => $this->agencyData($contract->agence))->values()]);
    }

    public function attachAgency(Request $request): JsonResponse
    {
        $data = $request->validate(['code' => ['required', 'string', 'max:150']]);
        $locataire = $request->attributes->get('mobile_actor');
        $agency = Agence::where('code_agence', trim($data['code']))->first();

        if (! $agency) {
            throw ValidationException::withMessages(['code' => "Ce code d'agence est invalide."]);
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

            return [
                'contract_id' => $contract->getKey(),
                'property_id' => $contract->propriete_id,
                'title' => $contract->propriete?->reference ?? 'Bien immobilier',
                'type' => $contract->propriete?->typePropriete?->name,
                'location' => $contract->propriete?->adresse_complete,
                'building' => $contract->batiment?->name,
                'door' => $contract->porte?->numero_porte,
                'monthly_rent' => (int) ($contract->loyer_net ?: $contract->porte?->mt_loyer),
                'lease_started_at' => optional($contract->date_debut_bail)->toDateString(),
                'expected' => (int) (clone $loyers)->sum('montant_a_payer'),
                'paid' => (int) (clone $loyers)->sum('montant_payer'),
                'arrears' => (int) (clone $loyers)->sum('montant_restant'),
            ];
        })->values()]);
    }

    public function receipts(Request $request, string $agency): JsonResponse
    {
        $locataireId = $request->attributes->get('mobile_actor')->getKey();
        $this->assertAccess($locataireId, $agency);
        $data = $request->validate([
            'property_id' => ['nullable', 'string'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
            'per_page' => ['nullable', 'integer', 'between:1,100'],
        ]);

        $query = TransactionAgence::with(['porte.batiment.propriete', 'modePaiement'])
            ->where('locataire_id', $locataireId)
            ->where('agence_id', $agency)
            ->where('type_transaction', 'loyer')
            ->when($data['property_id'] ?? null, fn ($q, $id) => $q->where('propriete_id', $id))
            ->when($data['from'] ?? null, fn ($q, $date) => $q->whereDate('date_transaction', '>=', $date))
            ->when($data['to'] ?? null, fn ($q, $date) => $q->whereDate('date_transaction', '<=', $date))
            ->orderByDesc('date_transaction');

        $page = $query->paginate($data['per_page'] ?? 20);
        $page->through(fn ($transaction) => [
            'id' => $transaction->getKey(),
            'reference' => 'REC-'.strtoupper(substr($transaction->getKey(), 0, 8)),
            'property_id' => $transaction->propriete_id,
            'property' => $transaction->porte?->batiment?->propriete?->reference,
            'door' => $transaction->porte?->numero_porte,
            'amount' => (int) $transaction->montant_global_verser,
            'rent_amount' => (int) $transaction->montant_loyer_payer,
            'arrears_amount' => (int) $transaction->montant_arriere_payer,
            'paid_periods' => $this->decodePeriods($transaction->mois_payer),
            'payment_method' => $transaction->modePaiement?->name,
            'date' => optional($transaction->date_transaction)->toIso8601String(),
        ]);

        return response()->json($page);
    }

    private function assertAccess(string $locataireId, string $agencyId): void
    {
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

    private function decodePeriods(?string $periods): array
    {
        $decoded = json_decode($periods ?: '[]', true);

        return is_array($decoded) ? $decoded : [];
    }
}
