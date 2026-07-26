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

        $auth = Auth::guard($guard);

        // Bloquer proprement les visiteurs non connectés avant de tester la permission
        if (!$auth->check()) {
            return redirect()->route($guard === 'admin' ? 'admin.login' : 'agence.login');
        }

        // Vérifier si l'utilisateur a la permission demandée
        if (!$auth->user()->hasPermission($permission)) {
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
