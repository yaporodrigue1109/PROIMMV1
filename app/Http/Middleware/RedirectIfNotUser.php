<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class RedirectIfNotUser
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $guard = 'user'): Response
    {
//        dd([
//            'guard'        => $guard,
//            'check'        => Auth::guard($guard)->check(),
//            'user'         => Auth::guard($guard),
//            'session_id'   => session()->getId(),
//            'session_all'  => session()->all(),
//        ]);
        if (!Auth::guard($guard)->check()) {
            return redirect()->route('agence.login')->with('error', 'Votre session a expiré.');
        }

        $user = Auth::guard($guard)->user();

        // Deux gardes peuvent techniquement cohabiter dans une session Laravel.
        // On ne conserve la garde admin que pour une usurpation explicitement
        // initiée depuis l'administration et correspondant à cet utilisateur.
        if (Auth::guard('admin')->check()) {
            $admin = Auth::guard('admin')->user();
            $impersonation = $request->session()->get('admin_agency_impersonation');
            $isValidImpersonation = is_array($impersonation)
                && (string) data_get($impersonation, 'admin_id') === (string) $admin->getAuthIdentifier()
                && (string) data_get($impersonation, 'user_id') === (string) $user->getAuthIdentifier()
                && (string) data_get($impersonation, 'agence_id') === (string) $user->agence_id;

            if (! $isValidImpersonation) {
                Auth::guard('admin')->logout();
                $request->session()->forget('admin_agency_impersonation');
                $request->session()->regenerate();
            }
        }

        if ($user->statut !== 'actif') {
            Auth::guard($guard)->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('agence.login')
                ->with('error', 'Votre compte a été désactivé. Vous avez été déconnecté.');
        }

        if (! $user->agence || $user->agence->statut === 'desactive') {
            Auth::guard($guard)->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('agence.login')
                ->with('error', 'Votre agence est désactivée. Vous avez été déconnecté.');
        }

        return $next($request);
    }
}
