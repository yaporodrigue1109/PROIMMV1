<?php

namespace App\Http\Middleware;

use App\Models\AgenceActivityLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class LogAgencyActivity
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::guard('user')->user();
        $shouldLog = $user
            && ! in_array($request->method(), ['GET', 'HEAD', 'OPTIONS'], true)
            && ! $request->routeIs('agence.parametrage.journal.*')
            && Schema::hasTable('agence_activity_logs');

        $response = $next($request);

        if (! $shouldLog || $response->getStatusCode() >= 400) {
            return $response;
        }

        $journalEnabled = ! Schema::hasTable('parametrages_agence')
            || (bool) ($user->agence?->parametrage?->journal_activites ?? true);

        if (! $journalEnabled) {
            return $response;
        }

        $routeName = $request->route()?->getName();
        $action = match (true) {
            $request->routeIs('agence.logout') => 'deconnexion',
            $request->isMethod('POST') => 'creation',
            $request->isMethod('PUT'), $request->isMethod('PATCH') => 'modification',
            $request->isMethod('DELETE') => 'suppression',
            default => 'action',
        };

        try {
            AgenceActivityLog::query()->create([
                'agence_id' => $user->agence_id,
                'user_id' => $user->getAuthIdentifier(),
                'user_name' => $user->name,
                'action' => $action,
                'description' => $this->description($routeName, $action),
                'route_name' => $routeName,
                'method' => $request->method(),
                'path' => '/'.$request->path(),
                'ip_address' => $request->ip(),
                'user_agent' => mb_substr((string) $request->userAgent(), 0, 1000),
                'context' => [
                    'parameters' => collect($request->route()?->parameters() ?? [])
                        ->map(fn ($value) => is_object($value) && method_exists($value, 'getRouteKey') ? $value->getRouteKey() : $value)
                        ->all(),
                ],
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            report($e);
        }

        return $response;
    }

    private function description(?string $routeName, string $action): string
    {
        $resource = collect(explode('.', (string) $routeName))
            ->reject(fn ($part) => in_array($part, ['agence', 'store', 'update', 'destroy'], true))
            ->map(fn ($part) => str_replace('-', ' ', $part))
            ->first() ?: 'élément';

        return match ($action) {
            'creation' => "Création : {$resource}",
            'modification' => "Modification : {$resource}",
            'suppression' => "Suppression : {$resource}",
            'deconnexion' => 'Déconnexion de l’espace agence',
            default => "Action : {$resource}",
        };
    }
}
