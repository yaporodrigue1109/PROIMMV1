<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

// ── Global ─────────────────────────────────────────────────────
use App\Repositories\Interfaces\AgenceRepositoryInterface;
use App\Repositories\Repository\AgenceRepository;

use App\Repositories\Interfaces\AbonnementRepositoryInterface;
use App\Repositories\Repository\AbonnementRepository;

use App\Repositories\Interfaces\TransactionRepositoryInterface;
use App\Repositories\Repository\TransactionRepository;

use App\Repositories\Interfaces\ProprietaireRepositoryInterface;
use App\Repositories\Repository\ProprietaireRepository;

use App\Repositories\Interfaces\SettingRepositoryInterface;
use App\Repositories\Repository\SettingRepository;
use App\Services\SettingService;

use App\Repositories\Interfaces\ConfigurationTarifRepositoryInterface;
use App\Repositories\Repository\ConfigurationTarifRepository;
use App\Services\ConfigurationTarifService;

use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Repositories\Repository\UserRepository;
use App\Services\UserService;

// ── Agence ─────────────────────────────────────────────────────
use App\Repositories\Agence\Interfaces\LotRepositoryInterface;
use App\Repositories\Agence\Repository\LotRepository;

use App\Repositories\Agence\Interfaces\PersonnelRepositoryInterface;
use App\Repositories\Agence\Repository\PersonnelRepository;

use App\Repositories\Agence\Interfaces\ProprieteRepositoryInterface;
use App\Repositories\Agence\Repository\ProprieteRepository;

use App\Repositories\Agence\Interfaces\BatimentRepositoryInterface;
use App\Repositories\Agence\Repository\BatimentRepository;

use App\Repositories\Agence\Interfaces\PorteRepositoryInterface;
use App\Repositories\Agence\Repository\PorteRepository;

// ── TypePropriete ──────────────────────────────────────────────
use App\Repositories\Agence\Interfaces\TypeProprieteRepositoryInterface;
use App\Repositories\Agence\Repository\TypeProprieteRepository;
use App\Repositories\Agence\Interfaces\EquipementProprieteRepositoryInterface;
use App\Repositories\Agence\Repository\EquipementProprieteRepository;

use App\Repositories\Agence\Interfaces\ProssimiteProprieteRepositoryInterface;
use App\Repositories\Agence\Repository\ProssimiteProprieteRepository;
use App\Services\Agence\TypeProprieteService;                     // ← le service séparé

use App\Repositories\Agence\Interfaces\LocataireRepositoryInterface;
use App\Repositories\Agence\Repository\LocataireRepository;

use App\Repositories\Agence\Interfaces\ParametrageAgenceRepositoryInterface;
use App\Repositories\Agence\Repository\ParametrageAgenceRepository;

use App\Repositories\Agence\Interfaces\MaintenanceRepositoryInterface;
use App\Repositories\Agence\Repository\MaintenanceRepository;

use App\Repositories\Agence\Interfaces\MaintenancierRepositoryInterface;
use App\Repositories\Agence\Repository\MaintenancierRepository;

use App\Repositories\Agence\Interfaces\FonctionMaintenanceRepositoryInterface;
use App\Repositories\Agence\Repository\FonctionMaintenanceRepository;

use App\Repositories\Agence\Interfaces\TypeMaintenanceRepositoryInterface;
use App\Repositories\Agence\Repository\TypeMaintenanceRepository;

use App\Repositories\Agence\Interfaces\MaintenanceDetailRepositoryInterface;
use App\Repositories\Agence\Repository\MaintenanceDetailRepository;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(MaintenanceRepositoryInterface::class, MaintenanceRepository::class);
        $this->app->bind(MaintenancierRepositoryInterface::class, MaintenancierRepository::class);
        $this->app->bind(FonctionMaintenanceRepositoryInterface::class, FonctionMaintenanceRepository::class);
        $this->app->bind(TypeMaintenanceRepositoryInterface::class, TypeMaintenanceRepository::class);
        $this->app->bind(MaintenanceDetailRepositoryInterface::class,MaintenanceDetailRepository::class);

        // ── Repositories globaux ───────────────────────────────
        $this->app->bind(AgenceRepositoryInterface::class,       AgenceRepository::class);
        $this->app->bind(AbonnementRepositoryInterface::class,   AbonnementRepository::class);
        $this->app->bind(TransactionRepositoryInterface::class,  TransactionRepository::class);
        $this->app->bind(ProprietaireRepositoryInterface::class, ProprietaireRepository::class);
        $this->app->bind(SettingRepositoryInterface::class,      SettingRepository::class);
        $this->app->bind(ConfigurationTarifRepositoryInterface::class, ConfigurationTarifRepository::class);
        $this->app->bind(UserRepositoryInterface::class,         UserRepository::class);
        $this->app->bind(LotRepositoryInterface::class,          LotRepository::class);
        $this->app->bind(PersonnelRepositoryInterface::class,    PersonnelRepository::class);
        $this->app->bind(ParametrageAgenceRepositoryInterface::class, ParametrageAgenceRepository::class);
        // ── Repositories Agence / Propriété ───────────────────
        $this->app->bind(ProprieteRepositoryInterface::class,    ProprieteRepository::class);
        $this->app->bind(BatimentRepositoryInterface::class,     BatimentRepository::class);
        $this->app->bind(PorteRepositoryInterface::class,        PorteRepository::class);
        $this->app->bind(LocataireRepositoryInterface::class, LocataireRepository::class);

        // ── TypePropriete ─────────────────────────────────────
        //
        // CORRECTION : l'interface Repository doit pointer vers le
        // Repository concret, PAS vers le Service.
        //
        $this->app->bind(
            TypeProprieteRepositoryInterface::class,
            TypeProprieteRepository::class        // ← Repository concret
        );

        $this->app->bind(
            EquipementProprieteRepositoryInterface::class,
            EquipementProprieteRepository::class        // ← Repository concret
        );
        $this->app->bind(
            ProssimiteProprieteRepositoryInterface::class,
            ProssimiteProprieteRepository::class        // ← Repository concret
        );

        // Le Service est instancié avec son Repository injecté automatiquement
        // grâce au binding ci-dessus. Pas besoin de closure.
        $this->app->bind(TypeProprieteService::class, TypeProprieteService::class);

        // ── Services (avec dépendances explicites) ─────────────
        $this->app->singleton(SettingService::class, function ($app) {
            return new SettingService(
                $app->make(SettingRepositoryInterface::class)
            );
        });

        $this->app->bind(ConfigurationTarifService::class, function ($app) {
            return new ConfigurationTarifService(
                $app->make(ConfigurationTarifRepositoryInterface::class)
            );
        });

        $this->app->bind(UserService::class, function ($app) {
            return new UserService(
                $app->make(UserRepositoryInterface::class)
            );
        });
    }

    public function boot(): void {}
}