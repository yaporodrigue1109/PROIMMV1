<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BlockDemoAgenceMutations
{
    public const MESSAGE = "Vous ne pouvez pas effectuer d'action sur la plateforme car vous êtes en mode démo. Effectuez votre abonnement pour activer votre compte.";

    public function handle(Request $request, Closure $next): Response
    {
        if ($request->isMethodSafe() || $this->isAllowedMutation($request)) {
            return $next($request);
        }

        $user = auth('user')->user();
        $agence = $user?->agence;

        if (! $agence || $agence->statut !== 'en_demo') {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => self::MESSAGE,
                'subscription_url' => route('agence.abonnement.index'),
            ], 403);
        }

        return back()->with('error', self::MESSAGE);
    }

    private function isAllowedMutation(Request $request): bool
    {
        return $request->routeIs('agence.logout')
            || $request->routeIs('agence.abonnement.*');
    }
}
