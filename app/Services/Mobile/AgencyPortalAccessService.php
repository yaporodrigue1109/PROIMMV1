<?php

namespace App\Services\Mobile;

use App\Models\Agence;

class AgencyPortalAccessService
{
    public const OWNER_PORTAL_LABEL = 'Portail propriétaire';
    public const TENANT_PORTAL_LABEL = 'Portail locataire';

    private array $cache = [];

    public function enabled(Agence|string $agency, string $role): bool
    {
        $agency = $agency instanceof Agence ? $agency : Agence::find($agency);
        if (! $agency || ! in_array($role, ['locataire', 'proprietaire'], true)) {
            return false;
        }

        $portalLabel = $role === 'proprietaire'
            ? self::OWNER_PORTAL_LABEL
            : self::TENANT_PORTAL_LABEL;
        $cacheKey = $agency->getKey().':'.$portalLabel;

        return $this->cache[$cacheKey] ??= $this->resolve($agency, $portalLabel);
    }

    public function optionsContainPortal(array $options, string $portalLabel, array $portalIds = []): bool
    {
        foreach ($options as $option) {
            if (is_scalar($option) && in_array((int) $option, $portalIds, true)) {
                return true;
            }

            if (! is_array($option)
                || ! filter_var($option['actif'] ?? true, FILTER_VALIDATE_BOOL)) {
                continue;
            }

            $hasMatchingId = in_array((int) ($option['id'] ?? 0), $portalIds, true);
            $hasMatchingLabel = mb_strtolower(trim((string) ($option['label'] ?? '')))
                === mb_strtolower($portalLabel);

            if ($hasMatchingId || $hasMatchingLabel) {
                return true;
            }
        }

        return false;
    }

    private function resolve(Agence $agency, string $portalLabel): bool
    {
        $today = today();
        $subscription = $agency->relationLoaded('abonnement')
            ? $agency->abonnement
            : $agency->abonnement()->first();

        if ($agency->statut !== 'active'
            || ! $subscription
            || $subscription->statut !== 'actif'
            || ! $agency->abonnement_end
            || $agency->abonnement_end->startOfDay()->isBefore($today)) {
            return false;
        }

        return $this->optionsContainPortal(
            $subscription->features ?? [],
            $portalLabel
        );
    }
}
