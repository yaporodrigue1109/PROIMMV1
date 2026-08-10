<?php

namespace App\Services\Mobile;

use App\Models\Agence;
use App\Models\ConfigurationTarifModule;
use App\Models\Transaction;

class AgencyPortalAccessService
{
    public const OWNER_PORTAL_ID = 5;

    public const TENANT_PORTAL_ID = 6;

    private array $cache = [];

    public function enabled(Agence|string $agency, string $role): bool
    {
        $agency = $agency instanceof Agence ? $agency : Agence::find($agency);
        if (! $agency || ! in_array($role, ['locataire', 'proprietaire'], true)) {
            return false;
        }

        $portalId = $role === 'proprietaire' ? self::OWNER_PORTAL_ID : self::TENANT_PORTAL_ID;
        $cacheKey = $agency->getKey().':'.$portalId;

        return $this->cache[$cacheKey] ??= $this->resolve($agency, $portalId);
    }

    public function optionsContainPortal(array $options, int $portalId): bool
    {
        foreach ($options as $option) {
            if (is_scalar($option) && (int) $option === $portalId) {
                return true;
            }

            if (is_array($option)
                && (int) ($option['id'] ?? 0) === $portalId
                && filter_var($option['actif'] ?? true, FILTER_VALIDATE_BOOL)) {
                return true;
            }
        }

        return false;
    }

    private function resolve(Agence $agency, int $portalId): bool
    {
        if ($agency->statut !== 'active'
            || ! $agency->abonnement_start
            || ! $agency->abonnement_end
            || $agency->abonnement_start->isFuture()
            || $agency->abonnement_end->isPast()) {
            return false;
        }

        $moduleIsAvailable = ConfigurationTarifModule::whereKey($portalId)
            ->where('actif', true)
            ->exists();
        if (! $moduleIsAvailable) {
            return false;
        }

        $subscription = Transaction::where('agence_id', $agency->getKey())
            ->whereNotIn('statut', ['echouee', 'remboursee', 'annulee'])
            ->whereDate('periode_debut', '<=', today())
            ->whereDate('periode_fin', '>=', today())
            ->latest('transaction_id')
            ->first();

        return $subscription
            && $this->optionsContainPortal($subscription->options_souscrites ?? [], $portalId);
    }
}
