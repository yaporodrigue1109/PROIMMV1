<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAgencyRoutePermission
{
    /**
     * Routes personnelles ou techniques qui ne dépendent pas d'un module métier.
     */
    private const EXEMPT_ROUTES = [
        'agence.logout',
        'agence.profile*',
        'agence.abonnement.*',
        'agence.aide*',
    ];

    /**
     * Certains préfixes de routes ne correspondent pas directement au slug du module.
     */
    private const MODULES = [
        'dashboard' => 'dashboard',
        'statistiques' => 'statistiques',
        'proprietes' => 'proprietes',
        'proprietaire' => 'proprietaires',
        'locataires' => 'locataires',
        'personnel' => 'personnel',
        'maintenance' => 'maintenance',
        'announcements' => 'annonce',
        'caisse' => 'caisse',
        'reversements' => 'reversement',
        'support' => 'support',
        'parametrage' => 'parametrage',
        'logs' => 'parametrage',
        'types-propriete' => 'parametrage',
        'equipement-propriete' => 'parametrage',
        'possimite-propriete' => 'parametrage',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $route = $request->route();
        $routeName = $route?->getName();

        if (!$routeName || $this->isExempt($routeName)) {
            return $next($request);
        }

        $user = $request->user('user');

        if (!$user) {
            abort(401, 'Vous devez être connecté.');
        }

        [$module, $action] = $this->permissionFor($routeName, $request->method());

        // Une nouvelle route métier doit être ajoutée explicitement à la table ci-dessus.
        // Le refus par défaut évite qu'une fonctionnalité soit publiée sans protection.
        if (!$module || !$action || !$user->canPerform($module, $action)) {
            abort(403, "Vous n'avez pas la permission nécessaire pour cette action.");
        }

        return $next($request);
    }

    public function permissionFor(string $routeName, string $method = 'GET'): array
    {
        $segments = explode('.', $routeName);
        $routeModule = $segments[1] ?? null;
        $module = self::MODULES[$routeModule] ?? null;

        if (!$module) {
            return [null, null];
        }

        $routeAction = implode('.', array_slice($segments, 2));

        if ($routeName === 'agence.caisse.loyer' || str_starts_with($routeName, 'agence.caisse.loyer.')) {
            $module = 'loyer';
        }

        $action = $this->actionFor($module, $routeAction, strtoupper($method));

        return [$module, $action];
    }

    private function actionFor(string $module, string $routeAction, string $method): string
    {
        if ($module === 'parametrage' && $method !== 'GET') {
            return 'edit';
        }

        if ($module === 'support' && preg_match('/(^|\.)reply$/', $routeAction)) {
            return 'edit';
        }

        if ($module === 'support' && preg_match('/(^|\.)close$/', $routeAction)) {
            return 'close';
        }

        if ($module === 'reversement' && str_ends_with($routeAction, 'marquer-reverse')) {
            return 'validate';
        }

        if ($module === 'reversement' && str_ends_with($routeAction, 'historique.pdf')) {
            return 'export';
        }

        if (preg_match('/(^|\.)(destroy|delete)$/', $routeAction)) {
            return 'delete';
        }

        if (preg_match('/(^|\.)(valider)$/', $routeAction)) {
            return 'validate';
        }

        if (preg_match('/(^|\.)(annuler)$/', $routeAction)) {
            return 'cancel';
        }

        if (preg_match('/(^|\.)(activate|deactivate)$/', $routeAction)) {
            return match ($module) {
                'personnel' => 'manage',
                'proprietaires' => 'activate',
                default => 'edit',
            };
        }

        if (preg_match('/(^|\.)(create|store)$/', $routeAction) || $method === 'POST') {
            return 'create';
        }

        if (preg_match('/(^|\.)(edit|update|statut|liberer|occuper)$/', $routeAction)
            || in_array($method, ['PUT', 'PATCH'], true)) {
            return 'edit';
        }

        return 'view';
    }

    private function isExempt(string $routeName): bool
    {
        foreach (self::EXEMPT_ROUTES as $pattern) {
            if (str_ends_with($pattern, '*')) {
                if (str_starts_with($routeName, substr($pattern, 0, -1))) {
                    return true;
                }

                continue;
            }

            if ($routeName === $pattern) {
                return true;
            }
        }

        return false;
    }
}
