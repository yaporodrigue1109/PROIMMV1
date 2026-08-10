<?php

namespace App\Http\Middleware;

use App\Models\Locataire;
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
