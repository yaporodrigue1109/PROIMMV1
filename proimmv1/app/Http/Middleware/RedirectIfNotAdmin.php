<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class RedirectIfNotAdmin
{
    public function handle(Request $request, Closure $next, $guard = 'admin'): Response
    {


        if (!Auth::guard($guard)->check()) {
            return redirect()->route('admin.login')->with('error', 'Votre session a expiré.');
        }

        return $next($request);
    }
}