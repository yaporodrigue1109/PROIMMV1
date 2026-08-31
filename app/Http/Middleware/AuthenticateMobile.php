<?php

namespace App\Http\Middleware;

use App\Models\Agence;
use App\Models\Locataire;
use App\Models\LocataireAgence;
use App\Models\MobileApiToken;
use App\Models\Proprietaire;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateMobile
{
    public function handle(Request $request, Closure $next, ?string $role = null): Response
    {


        $plainToken = $request->bearerToken();
        $token = $plainToken
            ? MobileApiToken::where('token_hash', hash('sha256', $plainToken))->first()
            : null;
//return response()->json(['plainToken' => $plainToken ], 401);
//return $this->unauthenticated($token);

        if (! $token || ($token->expires_at && $token->expires_at->isPast())) {
            return $this->unauthenticated('Jeton absent, invalide ou expiré.');
        }

        if ($role && $token->actor_type !== $role) {
            return response()->json(['message' => 'Ce compte ne peut pas accéder à cette ressource.'], 403);
        }

        $actor = match ($token->actor_type) {
            'locataire' => Locataire::find($token->actor_id),
            'proprietaire' => Proprietaire::find($token->actor_id),
            default => null,
        };

        if (! $actor) {
            $token->delete();

            return $this->unauthenticated('Le compte associé à ce jeton est introuvable.');
        }

        if ($token->actor_type === 'locataire') {
            $allAgencyLinks = LocataireAgence::withoutGlobalScopes()
                ->where('locataire_id', $actor->getKey());
            $agencyLinks = LocataireAgence::withoutGlobalScopes()
                ->where('locataire_id', $actor->getKey())
                ->where('is_active', true);
            $hasAnyLease = (clone $allAgencyLinks)->exists();
            $hasAgency = (clone $agencyLinks)->exists();

            if ($hasAnyLease && ! $hasAgency) {
                $token->delete();

                return $this->unauthenticated('Votre contrat a été désactivé. Vous avez été déconnecté.');
            }

            $hasActiveAgency = (clone $agencyLinks)
                ->whereHas('agency', fn ($query) => $query->where('statut', '!=', 'desactive'))
                ->exists();

            if ($hasAgency && ! $hasActiveAgency) {
                $token->delete();

                return $this->unauthenticated('Votre agence est désactivée. Vous avez été déconnecté.');
            }
        }

        if ($token->actor_type === 'proprietaire') {
            $allAgencyLinks = ProprietaireAgence::withTrashed()
                ->where('proprietaire_id', $actor->getKey());
            $activeAgencyLinks = ProprietaireAgence::query()
                ->where('proprietaire_id', $actor->getKey())
                ->where('is_active', true);
            $hasAnyMandate = (clone $allAgencyLinks)->exists();
            $hasActiveMandate = (clone $activeAgencyLinks)->exists();

            if ($hasAnyMandate && ! $hasActiveMandate) {
                $token->delete();

                return $this->unauthenticated('Votre compte propriétaire a été désactivé. Vous avez été déconnecté.');
            }

            $hasActiveAgency = (clone $activeAgencyLinks)
                ->whereHas('agence', fn ($query) => $query->where('statut', '!=', 'desactive'))
                ->exists();

            if ($hasActiveMandate && ! $hasActiveAgency) {
                $token->delete();

                return $this->unauthenticated('Votre agence est désactivée. Vous avez été déconnecté.');
            }
        }

        $agencyParameter = $request->route('agency');
        if ($agencyParameter) {
            $agency = $agencyParameter instanceof Agence
                ? $agencyParameter
                : Agence::query()
                    ->where('agence_id', $agencyParameter)
                    ->orWhere('code_agence', $agencyParameter)
                    ->first();

            if ($agency?->statut === 'desactive') {
                return response()->json([
                    'message' => 'Cette agence est désactivée et n\'est plus accessible.',
                ], 403);
            }
        }

        $token->forceFill(['last_used_at' => now()])->saveQuietly();
        $request->attributes->set('mobile_actor', $actor);
        $request->attributes->set('mobile_role', $token->actor_type);
        $request->attributes->set('mobile_token', $token);

        return $next($request);
    }

    private function unauthenticated(string $message): JsonResponse
    {
        return response()->json(['message' => $message], 401);
    }
}
