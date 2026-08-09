<?php

namespace Tests\Unit;

use App\Http\Middleware\CheckAgencyRoutePermission;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class CheckAgencyRoutePermissionTest extends TestCase
{
    #[DataProvider('routePermissions')]
    public function test_it_maps_agency_routes_to_module_actions(
        string $routeName,
        string $method,
        string $expectedModule,
        string $expectedAction,
    ): void {
        $middleware = new CheckAgencyRoutePermission();

        $this->assertSame(
            [$expectedModule, $expectedAction],
            $middleware->permissionFor($routeName, $method),
        );
    }

    public static function routePermissions(): array
    {
        return [
            'dashboard' => ['agence.dashboard', 'GET', 'dashboard', 'view'],
            'statistics' => ['agence.statistiques.index', 'GET', 'statistiques', 'view'],
            'property creation form' => ['agence.proprietes.create', 'GET', 'proprietes', 'create'],
            'property creation' => ['agence.proprietes.store', 'POST', 'proprietes', 'create'],
            'property update' => ['agence.proprietes.update', 'PUT', 'proprietes', 'edit'],
            'property deletion' => ['agence.proprietes.destroy', 'DELETE', 'proprietes', 'delete'],
            'owner activation' => ['agence.proprietaire.activate', 'PATCH', 'proprietaires', 'activate'],
            'owner deactivation' => ['agence.proprietaire.deactivate', 'PATCH', 'proprietaires', 'activate'],
            'staff management' => ['agence.personnel.deactivate', 'PATCH', 'personnel', 'manage'],
            'maintenance validation' => ['agence.maintenance.statut', 'PATCH', 'maintenance', 'edit'],
            'rent list' => ['agence.caisse.loyer', 'GET', 'loyer', 'view'],
            'rent payment' => ['agence.caisse.loyer.payer', 'POST', 'loyer', 'create'],
            'remittance validation' => ['agence.reversements.valider', 'POST', 'reversement', 'validate'],
            'remittance cancellation' => ['agence.reversements.annuler', 'POST', 'reversement', 'cancel'],
            'remittance export' => ['agence.reversements.historique.pdf', 'GET', 'reversement', 'export'],
            'support reply' => ['agence.support.reply', 'POST', 'support', 'edit'],
            'support closure' => ['agence.support.close', 'PATCH', 'support', 'close'],
            'settings update' => ['agence.parametrage.general.update', 'PUT', 'parametrage', 'edit'],
            'settings catalog deletion' => ['agence.types-propriete.destroy', 'DELETE', 'parametrage', 'edit'],
        ];
    }

    public function test_unknown_agency_module_is_denied_by_default(): void
    {
        $middleware = new CheckAgencyRoutePermission();

        $this->assertSame([null, null], $middleware->permissionFor('agence.inconnu.index'));
    }
}
