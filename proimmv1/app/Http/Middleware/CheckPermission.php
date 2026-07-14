<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckPermission
{
    public function handle(Request $request, Closure $next, $permission)
    {
        // Déterminer le guard selon la route
        $guard = $this->getGuardFromRoute($request);

        // Vérifier si l'utilisateur est authentifié
        if ($guard === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        if ($guard === 'user') {
            return redirect()->route('agence.dashboard');
        }
      //  dd(Auth::guard($guard)->user()->hasPermission($permission),$permission);
        // Vérifier si l'utilisateur a la permission
        if (!Auth::guard($guard)->user()->hasPermission($permission)) {
            abort(403, 'Vous n\'avez pas la permission nécessaire.');
        }

        return $next($request);
    }

    private function getGuardFromRoute($request)
    {
        $prefix = $request->route()?->getPrefix() ?? '';

        if (str_contains($prefix, 'admin')) {
            return 'admin';
        } elseif (str_contains($prefix, 'agence')) {
            return 'agence';
        }

        return 'web';
    }
}