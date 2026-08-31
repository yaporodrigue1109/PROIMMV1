<?php

namespace App\Http\Controllers\Agence\Logs;

use App\Http\Controllers\Controller;
use App\Models\LocataireAgence;
use App\Models\Porte;
use App\Models\ProprietaireAgence;
use App\Models\User;
use App\Repositories\Agence\Interfaces\LocataireRepositoryInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class LogsController extends Controller
{
    public function __construct(private LocataireRepositoryInterface $locataires) {}

    public function index(): Response
    {
        $agenceId = $this->agenceId();

        $owners = ProprietaireAgence::withTrashed()
            ->with('proprietaire')
            ->where('agence_id', $agenceId)
            ->where(fn ($query) => $query->where('is_active', false)->orWhereNotNull('deleted_at'))
            ->latest('date_desactivation')
            ->get()
            ->map(fn ($link) => [
                'link_id' => $link->proprietaire_agence_id,
                'id' => $link->proprietaire_id,
                'name' => $link->proprietaire?->name,
                'code' => $link->proprietaire?->code,
                'phone' => $link->proprietaire?->tel1,
                'disabled_at' => optional($link->date_desactivation ?? $link->deleted_at)->toIso8601String(),
                'deleted' => $link->trashed(),
            ]);

        $leases = LocataireAgence::withoutGlobalScopes()
            ->with(['locataire', 'porte', 'propriete', 'batiment'])
            ->where('agence_id', $agenceId)
            ->where('is_active', false)
            ->whereNotIn('locataire_id', LocataireAgence::withoutGlobalScopes()
                ->select('locataire_id')
                ->where('agence_id', $agenceId)
                ->where('is_active', true))
            ->latest('updated_at')
            ->get()
            ->unique('locataire_id')
            ->values()
            ->map(fn ($lease) => [
                'id' => $lease->locataire_id,
                'name' => $lease->locataire?->name,
                'code' => $lease->locataire?->code,
                'phone' => $lease->locataire?->tel1,
                'previous_door_id' => $lease->porte_id,
                'previous_door' => $lease->porte?->numero_porte,
                'previous_property' => $lease->propriete?->reference,
                'disabled_at' => optional($lease->updated_at)->toIso8601String(),
            ]);

        $doors = Porte::query()
            ->with('batiment.propriete')
            ->where('is_actif', true)
            ->where('is_occupe', false)
            ->whereHas('batiment.propriete', fn ($query) => $query->where('agence_id', $agenceId)->where('is_actif', true))
            ->get()
            ->map(fn (Porte $door) => [
                'id' => $door->porte_id,
                'label' => trim(($door->batiment?->propriete?->reference ?? 'Propriété').' · '.($door->batiment?->name ?? 'Bâtiment').' · Porte '.$door->numero_porte),
            ])
            ->values();

        $personnel = User::query()
            ->with('role')
            ->where('agence_id', $agenceId)
            ->where('statut', 'inactif')
            ->orderByDesc('updated_at')
            ->get()
            ->map(fn (User $user) => [
                'id' => $user->getKey(),
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->tel1,
                'role' => $user->role?->name ?: 'Sans rôle',
                'disabled_at' => optional($user->updated_at)->toIso8601String(),
            ]);

        return Inertia::render('Agence/Logs/Index', [
            'owners' => $owners,
            'tenants' => $leases,
            'availableDoors' => $doors,
            'personnel' => $personnel,
        ]);
    }

    public function restoreOwner(string $link): RedirectResponse
    {
        $ownerLink = ProprietaireAgence::withTrashed()
            ->where('agence_id', $this->agenceId())
            ->where('proprietaire_agence_id', $link)
            ->firstOrFail();

        DB::transaction(function () use ($ownerLink): void {
            if ($ownerLink->trashed()) {
                $ownerLink->restore();
            }
            $ownerLink->forceFill([
                'is_active' => true,
                'is_mobile_visible' => false,
                'date_activation' => now(),
                'date_desactivation' => null,
                'agent_activation_id' => getInfoAgent()?->users?->id_users,
            ])->save();
        });

        return back()->with('success', 'Propriétaire et propriétés réactivés avec succès.');
    }

    public function restoreTenant(Request $request, string $tenant): RedirectResponse
    {
        $data = $request->validate(['porte_id' => ['required', 'string']]);
        $agenceId = $this->agenceId();

        $oldLease = LocataireAgence::withoutGlobalScopes()
            ->with('locataire')
            ->where('agence_id', $agenceId)
            ->where('locataire_id', $tenant)
            ->where('is_active', false)
            ->latest('updated_at')
            ->firstOrFail();

        $alreadyActive = LocataireAgence::withoutGlobalScopes()
            ->where('agence_id', $agenceId)->where('locataire_id', $tenant)->where('is_active', true)->exists();
        if ($alreadyActive) {
            throw ValidationException::withMessages(['porte_id' => 'Ce locataire est déjà actif dans cette agence.']);
        }

        $door = Porte::with('batiment.propriete')
            ->where('porte_id', $data['porte_id'])->where('is_actif', true)->where('is_occupe', false)
            ->whereHas('batiment.propriete', fn ($query) => $query->where('agence_id', $agenceId)->where('is_actif', true))
            ->first();
        if (! $door || ! $door->batiment?->propriete) {
            throw ValidationException::withMessages(['porte_id' => "Cette porte n'est plus disponible."]);
        }

        $contractData = collect($oldLease->getAttributes())
            ->except(['locataire_agence_id', 'created_at', 'updated_at', 'created_by', 'updated_by', 'is_active', 'is_mobile_visible'])
            ->merge([
                'porte_id' => $door->porte_id,
                'batiment_id' => $door->batiment_id,
                'propriete_id' => $door->batiment->propriete_id,
                'proprietaire_id' => $door->batiment->propriete->proprietaire_id,
                'lot_id' => $door->batiment->propriete->lot_id,
                'date_debut_bail' => now()->toDateString(),
                'date_fin_bail' => null,
                'date_entree' => now()->toDateString(),
                'is_mobile_visible' => false,
            ])->all();

        $this->locataires->createContrat($oldLease->locataire, $contractData, false);

        return back()->with('success', 'Locataire réactivé et affecté à la porte sélectionnée.');
    }

    public function restorePersonnel(string $user): RedirectResponse
    {
        $member = User::query()
            ->where('agence_id', $this->agenceId())
            ->where('statut', 'inactif')
            ->whereKey($user)
            ->firstOrFail();

        $member->forceFill([
            'statut' => 'actif',
            'updated_by' => getInfoAgent()?->users?->id_users,
        ])->save();

        return back()->with('success', 'Membre du personnel réactivé avec succès.');
    }

    private function agenceId(): string
    {
        return (string) (getInfoAgent()?->users?->agence_id ?? auth('user')->user()?->agence_id);
    }
}
