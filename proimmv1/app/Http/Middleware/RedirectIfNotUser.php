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

        return $next($request);
    }
}