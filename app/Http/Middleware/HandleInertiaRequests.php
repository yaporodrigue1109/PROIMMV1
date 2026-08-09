<?php

namespace App\Http\Middleware;

use App\Models\Module;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function share(Request $request): array
    {
        $admin = Auth::guard('admin')->user();
        $user = Auth::guard('user')->user();
        $user?->loadMissing(['agence']);

        return array_merge(parent::share($request), [
            'appName' => config('app.name'),
            'auth' => [
                'admin' => $admin ? [
                    'id_admin' => $admin->id_admin,
                    'name' => $admin->name,
                    'email' => $admin->email,
                    'phone' => $admin->phone,
                    'statut' => $admin->statut,
                ] : null,
                'user' => $user ? [
                    'id_users' => $user->id_users,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'statut' => $user->statut,
                    'agence_id' => $user->agence_id,
                    'agence' => $user->agence ? [
                        'agence_id' => $user->agence->agence_id,
                        'name' => $user->agence->name,
                        'code_agence' => $user->agence->code_agence,
                        'statut' => $user->agence->statut,
                        'is_demo' => $user->agence->statut === 'en_demo' && ! $user->agence->abonnement_id,
                        'abonnement_id' => $user->agence->abonnement_id,
                        'abonnement_start' => $user->agence->abonnement_start,
                        'abonnement_end' => $user->agence->abonnement_end,
                    ] : null,
                ] : null,
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
            'navigationModules' => fn () => $this->navigationModules($user),
        ]);
    }

    private function navigationModules(?User $user): ?array
    {
        if (!$user || !Schema::hasTable('modules')) {
            return null;
        }

        $modules = Module::query()
            ->parents()
            ->active()
            ->with('activeChildren')
            ->orderBy('order_index')
            ->get();

        if (
            Schema::hasTable('role_permissions')
            && !$user->role?->grantsAllPermissions()
        ) {
            $allowedModuleIds = $user->role
                ?->allowedPermissions()
                ->whereHas('moduleAction', fn ($query) => $query
                    ->active()
                    ->where('slug', 'view'))
                ->pluck('module_id')
                ->unique()
                ?? collect();

            $modules = $modules->filter(function (Module $module) use ($allowedModuleIds): bool {
                return $allowedModuleIds->contains($module->module_id)
                    || $module->activeChildren->contains(
                        fn (Module $child): bool => $allowedModuleIds->contains($child->module_id)
                    );
            });
        }

        return $modules
            ->filter(fn (Module $module): bool => !empty($module->route) && Route::has($module->route))
            ->map(function (Module $module): array {
                $href = route($module->route);

                return [
                    'id' => $module->module_id,
                    'label' => $module->name,
                    'slug' => $module->slug,
                    'href' => $href,
                    'activeMatch' => parse_url($href, PHP_URL_PATH) ?: $href,
                ];
            })
            ->values()
            ->all();
    }
}
