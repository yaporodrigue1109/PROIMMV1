<?php

namespace App\Http\Controllers\Agence\Parametrage;

use App\Http\Controllers\Controller;
use App\Http\Requests\Agence\UpdateParametrageGeneralRequest;
use App\Http\Requests\Agence\UpdateParametrageFacturationRequest;
use App\Http\Requests\Agence\UpdateParametrageLogosRequest;
use App\Http\Requests\Agence\UpdateParametrageSignaturesRequest;
use App\Http\Requests\Agence\UpdateParametrageNotificationsRequest;
use App\Models\Agence;
use App\Models\Region;
use App\Models\Ville;
use App\Models\ModePaiement;
use App\Models\Module;
use App\Models\ModuleAction;
use App\Models\Role;
use App\Models\RolePermission;
use App\Models\User;
use Illuminate\Http\Request;
use App\Repositories\Agence\Interfaces\ParametrageAgenceRepositoryInterface;
use App\Repositories\Agence\Repository\ParametrageAgenceRepository;
use App\Repositories\Interfaces\AgenceRepositoryInterface;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ParametrageController extends Controller
{
    protected  $parametrageRepository;
    protected  $agenceRepository;

    public function __construct(ParametrageAgenceRepositoryInterface $parametrageRepository,AgenceRepositoryInterface $agenceRepository)
    {
        $this->parametrageRepository = $parametrageRepository;
        $this->agenceRepository = $agenceRepository;
    }

    /**
     * Afficher la page de paramétrage
     */
    public function index()
    {
        $agenceId = $this->agenceId();
        $agence = $this->agenceRepository->findById($agenceId);
        if (method_exists($agence, 'loadMissing')) {
            $agence->loadMissing(['region', 'ville', 'parametrage']);
        }

        $regions = Region::all();
        $villes = Ville::where('region_id', $agence->region_id)->get() ?? [];
        $modePaiement = ModePaiement::all();

        $parametrage = $this->parametrageRepository->getByAgence($agenceId);

        return Inertia::render('Agence/Parametrage/Index', [
            'parametrage' => $parametrage,
            'agence' => $agence,
            'regions' => $regions,
            'villes' => $villes,
            'modePaiement' => $modePaiement,
            ...$this->roleConfiguration($agenceId),
        ]);
    }

    public function storeRole(Request $request)
    {
        $this->authorizeRoleManagement();
        $agenceId = $this->agenceId();
        $data = $this->validateRolePayload($request);

        DB::transaction(function () use ($data, $agenceId): void {
            $role = Role::query()->create([
                'name' => $data['name'],
                'slug' => Str::slug($data['name']).'-'.Str::lower(Str::random(8)),
                'description' => $data['description'] ?? null,
                'agence_id' => $agenceId,
                'is_active' => true,
                'created_by' => Auth::guard('user')->id(),
            ]);

            $this->syncRolePermissions($role, $data['permissions']);
        });

        return redirect()->route('agence.parametrage.index', ['tab' => 'roles'])
            ->with('success', 'Rôle créé avec succès.');
    }

    public function updateRole(Request $request, string $roleId)
    {
        $this->authorizeRoleManagement();
        $sourceRole = $this->roleAvailableForEditing($roleId);
        $data = $this->validateRolePayload($request, $sourceRole);

        DB::transaction(function () use ($sourceRole, $data): void {
            $role = $this->materializeAgencyRole($sourceRole);
            $role->update([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'updated_by' => Auth::guard('user')->id(),
            ]);
            $this->syncRolePermissions($role, $data['permissions']);
        });

        return redirect()->route('agence.parametrage.index', ['tab' => 'roles'])
            ->with('success', 'Rôle et permissions enregistrés avec succès.');
    }

    public function destroyRole(string $roleId)
    {
        $this->authorizeRoleManagement();
        $role = $this->deletableRole($roleId);

        if ($role->users()->exists()) {
            return back()->with('error', 'Ce rôle est encore attribué à un ou plusieurs utilisateurs.');
        }

        $role->delete();

        return redirect()->route('agence.parametrage.index', ['tab' => 'roles'])
            ->with('success', 'Rôle supprimé avec succès.');
    }

    /**
     * Mettre à jour les paramètres généraux
     */
    public function updateAgence(Request $request)
    {
        try {
            $agenceId = $this->agenceId();
            $this->agenceRepository->update($agenceId, $request->all());
            //$this->parametrageRepository->updateGeneral($agenceId, $request->validated());

            return redirect()->back()->with('success', 'Paramètres généraux mis à jour avec succès.');
        } catch (\Exception $e) {
            dd($e->getMessage());
            return redirect()->back()->with('error', 'Une erreur est survenue : ' . $e->getMessage());
        }
    }

    /**
     * Mettre à jour les paramètres de facturation
     */
    public function updateFacturation(Request $request)
    {
        try {
           // dd($request->all());
            $agenceId = $this->agenceId();
            $this->parametrageRepository->updateFacturation($agenceId, $request->all());

            return redirect()->back()->with('success', 'Paramètres de facturation mis à jour avec succès.');
        } catch (\Exception $e) {
            dd($e->getMessage());
            return redirect()->back()->with('error', 'Une erreur est survenue : ' . $e->getMessage());
        }
    }

    /**
     * Mettre à jour les logos
     */
    public function updateLogos(UpdateParametrageLogosRequest $request)
    {
        try {
            $agenceId = $this->agenceId();
            $files = $request->file();
            $data = $request->validated();

            $this->parametrageRepository->updateLogos($agenceId, $files, $data);

            return redirect()->back()->with('success', 'Logos mis à jour avec succès.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Une erreur est survenue : ' . $e->getMessage());
        }
    }

    /**
     * Mettre à jour les signatures
     */
    public function updateSignatures(UpdateParametrageSignaturesRequest $request)
    {
        try {
            $agenceId = $this->agenceId();
            $files = $request->file();
            $data = $request->validated();

            $this->parametrageRepository->updateSignatures($agenceId, $files, $data);

            return redirect()->back()->with('success', 'Signatures mises à jour avec succès.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Une erreur est survenue : ' . $e->getMessage());
        }
    }

    /**
     * Mettre à jour les notifications
     */
    public function updateNotifications(UpdateParametrageNotificationsRequest $request)
    {
        try {
            $agenceId = $this->agenceId();
            $this->parametrageRepository->updateNotifications($agenceId, $request->validated());

            return redirect()->back()->with('success', 'Paramètres de notification mis à jour avec succès.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Une erreur est survenue : ' . $e->getMessage());
        }
    }

    public function updateGeneral(Request $request)
    {
        try {
            $agenceId = $this->agenceId();
            $this->parametrageRepository->updateGeneral($agenceId, $request->all());

            return redirect()->back()->with('success', 'Paramètres de notification mis à jour avec succès.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Une erreur est survenue : ' . $e->getMessage());
        }
    }

    private function roleConfiguration(string $agenceId): array
    {
        if (!Schema::hasTable('roles') || !Schema::hasTable('modules') || !Schema::hasTable('module_actions') || !Schema::hasTable('role_permissions')) {
            return [
                'agencyRoles' => [],
                'permissionGroups' => [],
                'rolePermissions' => [],
            ];
        }

        $actions = ModuleAction::query()
            ->active()
            ->whereHas('module', fn ($query) => $query->active())
            ->get();
        $allActionIds = $actions->pluck('module_action_id')->values()->all();

        $roles = Role::query()
            ->forAgence($agenceId)
            ->withoutAdmin()
            ->withCount(['users' => fn ($query) => $query->where('agence_id', $agenceId)])
            ->orderByRaw("CASE WHEN agence_id IS NULL OR agence_id = '' THEN 0 ELSE 1 END")
            ->orderBy('name')
            ->get();

        $overriddenRoleIds = $roles
            ->filter(fn (Role $role): bool => (string) $role->agence_id === (string) $agenceId && !empty($role->base_role_id))
            ->pluck('base_role_id');

        $roles = $roles->reject(fn (Role $role): bool => empty($role->agence_id) && $overriddenRoleIds->contains($role->role_id));

        $permissions = [];
        foreach ($roles as $role) {
            $permissions[$role->role_id] = $role->grantsAllPermissions()
                ? $allActionIds
                : $role->allowedPermissions()
                    ->whereIn('module_action_id', $allActionIds)
                    ->pluck('module_action_id')
                    ->values()
                    ->all();
        }

        $modules = Module::query()
            ->active()
            ->with(['parent', 'activeActions'])
            ->orderBy('parent_id')
            ->orderBy('order_index')
            ->get();
        $responsableUserCount = User::query()
            ->where('agence_id', $agenceId)
            ->where('is_responsable', true)
            ->count();

        return [
            'agencyRoles' => $roles->map(function (Role $role) use ($agenceId, $responsableUserCount): array {
                $isCustom = (string) $role->agence_id === (string) $agenceId;
                $isPredefined = !$isCustom || !empty($role->base_role_id);

                return [
                    'role_id' => $role->role_id,
                    'name' => $role->name,
                    'description' => $role->description ?: 'Rôle du personnel de l’agence.',
                    'is_custom' => !$isPredefined,
                    'is_predefined' => $isPredefined,
                    'is_editable' => true,
                    'is_deletable' => $isCustom && !$isPredefined,
                    'is_responsable' => $role->isResponsable(),
                    'user_count' => $role->isResponsable()
                        ? $responsableUserCount
                        : $role->users_count,
                ];
            })->values()->all(),
            'permissionGroups' => $modules
                ->filter(fn (Module $module): bool => $module->activeActions->isNotEmpty())
                ->map(fn (Module $module): array => [
                    'module_id' => $module->module_id,
                    'label' => $module->parent
                        ? "{$module->parent->name} — {$module->name}"
                        : $module->name,
                    'permissions' => $module->activeActions->map(fn (ModuleAction $action): array => [
                        'key' => $action->module_action_id,
                        'label' => $action->name,
                        'slug' => $action->slug,
                        'module_slug' => $module->slug,
                        'is_critical' => (bool) ($action->is_critical ?? false),
                    ])->values()->all(),
                ])->values()->all(),
            'rolePermissions' => $permissions,
        ];
    }

    private function validateRolePayload(Request $request, ?Role $role = null): array
    {
        $nameRule = Rule::unique('roles', 'name')->where(function ($query) use ($role): void {
            $query->where(fn ($scope) => $scope
                ->where('agence_id', $this->agenceId())
                ->orWhereNull('agence_id')
                ->orWhere('agence_id', ''));

            if ($role?->base_role_id) {
                $query->where('role_id', '<>', $role->base_role_id);
            }
        });

        if ($role) {
            $nameRule->ignore($role->role_id, 'role_id');
        }

        return $request->validate([
            'name' => [
                'required',
                'string',
                'max:150',
                $nameRule,
            ],
            'description' => ['nullable', 'string', 'max:500'],
            'permissions' => ['present', 'array'],
            'permissions.*' => ['uuid', 'distinct', Rule::exists('module_actions', 'module_action_id')],
        ]);
    }

    private function syncRolePermissions(Role $role, array $actionIds): void
    {
        if ($role->grantsAllPermissions()) {
            $actionIds = ModuleAction::query()
                ->active()
                ->whereHas('module', fn ($query) => $query->active())
                ->pluck('module_action_id')
                ->all();
        }

        if ($role->isResponsable()) {
            $parametrageActionIds = ModuleAction::query()
                ->active()
                ->whereHas('module', fn ($query) => $query
                    ->active()
                    ->where('slug', 'parametrage'))
                ->pluck('module_action_id')
                ->all();

            $actionIds = array_values(array_unique([...$actionIds, ...$parametrageActionIds]));
        }

        $actions = ModuleAction::query()
            ->active()
            ->whereIn('module_action_id', $actionIds)
            ->whereHas('module', fn ($query) => $query->active())
            ->get(['module_action_id', 'module_id']);

        $role->rolePermissions()->delete();

        foreach ($actions as $action) {
            RolePermission::query()->create([
                'role_id' => $role->role_id,
                'module_id' => $action->module_id,
                'module_action_id' => $action->module_action_id,
                'is_allowed' => true,
                'created_by' => Auth::guard('user')->id(),
            ]);
        }
    }

    private function roleAvailableForEditing(string $roleId): Role
    {
        return Role::query()
            ->where('role_id', $roleId)
            ->where(function ($query): void {
                $query->where('agence_id', $this->agenceId())
                    ->orWhereNull('agence_id')
                    ->orWhere('agence_id', '');
            })
            ->whereNotIn('slug', ['admin', 'super-admin', 'super_admin'])
            ->firstOrFail();
    }

    private function materializeAgencyRole(Role $role): Role
    {
        if ((string) $role->agence_id === (string) $this->agenceId()) {
            if ($role->base_role_id) {
                $this->assignAgencyUsersToOverride($role);
            }

            return $role;
        }

        $agencyRole = Role::query()->firstOrCreate(
            [
                'agence_id' => $this->agenceId(),
                'base_role_id' => $role->role_id,
            ],
            [
                'name' => $role->name,
                'slug' => $role->slug.'-'.Str::lower(Str::random(8)),
                'description' => $role->description,
                'is_active' => true,
                'created_by' => Auth::guard('user')->id(),
            ]
        );

        if ($agencyRole->wasRecentlyCreated) {
            $this->syncRolePermissions(
                $agencyRole,
                $role->allowedPermissions()->pluck('module_action_id')->all()
            );

        }

        $this->assignAgencyUsersToOverride($agencyRole);

        return $agencyRole;
    }

    private function assignAgencyUsersToOverride(Role $agencyRole): void
    {
        $query = User::query()
            ->where('agence_id', $this->agenceId())
            ->where(function ($users) use ($agencyRole): void {
                $users->where('role_id', $agencyRole->base_role_id);

                if ($agencyRole->isResponsable()) {
                    $users->orWhere('is_responsable', true);
                }
            });

        $query->update(['role_id' => $agencyRole->role_id]);
    }

    private function deletableRole(string $roleId): Role
    {
        return Role::query()
            ->where('role_id', $roleId)
            ->where('agence_id', $this->agenceId())
            ->whereNull('base_role_id')
            ->firstOrFail();
    }

    private function authorizeRoleManagement(): void
    {
        $user = Auth::guard('user')->user();
        abort_unless($user?->isResponsable() || $user?->role?->isResponsable(), 403);
    }

    public function agenceId(){

      return  getInfoAgent()->users->agence_id;
    }
}
