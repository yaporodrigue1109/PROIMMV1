<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BlockDemoAgenceMutations
{
    public const MESSAGE = "Vous ne pouvez pas effectuer d'action sur la plateforme car vous êtes en mode démo. Effectuez votre abonnement pour activer votre compte.";
    public const EXPIRED_MESSAGE = "Votre abonnement est arrivé à expiration. Vous pouvez consulter vos données, mais seul le réabonnement est autorisé.";

    public function handle(Request $request, Closure $next): Response
    {
        $user = auth('user')->user();
        $agence = $user?->agence;

        $subscriptionExpired = $agence?->abonnement_id
            && $agence?->abonnement_end
            && $agence->abonnement_end->startOfDay()->lt(now()->startOfDay());

        if ($subscriptionExpired && $agence->statut !== 'desactive' && $agence->statut !== 'fin_abonnement') {
            $agence->forceFill(['statut' => 'fin_abonnement'])->saveQuietly();
            $user->setRelation('agence', $agence);
        }

        if ($request->isMethodSafe()) {
            return $next($request);
        }

        if ($subscriptionExpired) {
            if ($this->isSubscriptionMutationAllowed($request)) {
                return $next($request);
            }

            return $this->blockedResponse($request, self::EXPIRED_MESSAGE);
        }

        if ($this->isDemoMutationAllowed($request)) {
            return $next($request);
        }

        if (! $agence || $agence->statut !== 'en_demo') {
            return $next($request);
        }

        return $this->blockedResponse($request, self::MESSAGE);
    }

    private function blockedResponse(Request $request, string $message): Response
    {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'subscription_url' => route('agence.abonnement.index'),
            ], 403);
        }

        return redirect()->route('agence.abonnement.index')
            ->with('error', $message);
    }

    private function isSubscriptionMutationAllowed(Request $request): bool
    {
        return $request->routeIs('agence.logout')
            || $request->routeIs('agence.abonnement.*');
    }

    private function isDemoMutationAllowed(Request $request): bool
    {
        return $request->routeIs('agence.logout')
            || $request->routeIs('agence.profile*')
            || $request->routeIs('agence.abonnement.*');
    }
}
