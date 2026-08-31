<?php

namespace App\Http\Middleware;

use App\Models\Module;
use App\Models\Pays;
use App\Models\Region;
use App\Models\Ville;
use App\Services\SettingService;
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
        $impersonation = $request->session()->get('admin_agency_impersonation');
        $isAgencyImpersonation = $admin
            && $user
            && is_array($impersonation)
            && (string) data_get($impersonation, 'admin_id') === (string) $admin->getAuthIdentifier()
            && (string) data_get($impersonation, 'user_id') === (string) $user->getAuthIdentifier()
            && (string) data_get($impersonation, 'agence_id') === (string) $user->agence_id;
        $user?->loadMissing(['agence.parametrage']);
        $configuration = app(SettingService::class)->getSetting();

        return array_merge(parent::share($request), [
            'appName' => config('app.name'),
            'branding' => [
                'name' => $configuration->name ?: config('app.name'),
                'logoUrl' => $configuration->logo_url,
            ],
            'siteConfig' => fn () => $this->publicSiteConfiguration($configuration),
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
                    'role' => $user->role ? [
                        'role_id' => $user->role->role_id,
                        'name' => $user->role->name,
                        'slug' => $user->role->slug,
                    ] : null,
                    'permissions' => $user->getPermissions(),
                    'agence' => $user->agence ? [
                        'agence_id' => $user->agence->agence_id,
                        'name' => $user->agence->name,
                        'code_agence' => $user->agence->code_agence,
                        'logo' => $user->agence->parametrage?->logo,
                        'statut' => $user->agence->statut,
                        'is_demo' => $user->agence->statut === 'en_demo' && ! $user->agence->abonnement_id,
                        'abonnement_id' => $user->agence->abonnement_id,
                        'abonnement_start' => $user->agence->abonnement_start,
                        'abonnement_end' => $user->agence->abonnement_end,
                        'abonnement_expire' => $user->agence->abonnement_expire,
                    ] : null,
                ] : null,
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'cash_report_url' => fn () => $request->session()->get('cash_report_url'),
            ],
            'agencyImpersonation' => fn () => $isAgencyImpersonation
                ? [
                    'active' => true,
                    'adminName' => data_get($impersonation, 'admin_name'),
                    'agencyName' => data_get($impersonation, 'agence_name'),
                    'stopUrl' => route('agence.impersonation.stop'),
                ]
                : null,
            'agencyPreferences' => fn () => $user ? [
                'doubleValidation' => (bool) ($user->agence?->parametrage?->double_validation ?? true),
                'activityLog' => (bool) ($user->agence?->parametrage?->journal_activites ?? true),
            ] : null,
            'navigationModules' => fn () => $this->navigationModules($user),
            'geography' => fn () => $this->geography($user),
        ]);
    }

    private function geography(?User $user = null): array
    {
        if (! Schema::hasTable('pays') || ! Schema::hasColumn('regions', 'pays_id')) {
            return ['countries' => [], 'regions' => [], 'cities' => [], 'defaultCountryCode' => 'CI'];
        }

        $defaultCountryCode = 'CI';
        $agencyRegionId = $user?->agence?->region_id;

        if ($agencyRegionId) {
            $countryId = Region::query()->whereKey($agencyRegionId)->value('pays_id');
            $defaultCountryCode = Pays::query()->whereKey($countryId)->value('iso2') ?: 'CI';
        }

        return [
            'defaultCountryCode' => strtoupper($defaultCountryCode),
            'countries' => Pays::query()->where('actif', true)->orderBy('name')->get(['id', 'name', 'iso2', 'indicatif']),
            'regions' => Region::query()->orderBy('name')->get(['id', 'name', 'pays_id']),
            'cities' => Ville::query()->orderBy('name')->get(['id', 'name', 'region_id']),
        ];
    }

    private function publicSiteConfiguration($configuration): array
    {
        return [
            'name' => $configuration->name ?: config('app.name'),
            'companyName' => $configuration->raison_social ?: $configuration->name,
            'logoUrl' => $configuration->logo_url,
            'faviconUrl' => $configuration->favicon_url,
            'email' => $configuration->email1,
            'secondaryEmail' => $configuration->email2,
            'phone' => $configuration->contact1,
            'secondaryPhone' => $configuration->contact2,
            'address' => $configuration->adresse,
            'postalBox' => $configuration->boite_postal,
            'website' => $configuration->site_web,
            'social' => [
                'facebook' => $configuration->facebook,
                'instagram' => $configuration->instagram,
                'linkedin' => $configuration->linkedin,
                'twitter' => $configuration->twitter,
                'google' => $configuration->google,
            ],
        ];
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
