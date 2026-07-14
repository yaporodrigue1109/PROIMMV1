<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    public function handle(Request $request, Closure $next, ...$guards): Response
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {

                if ($guard === 'admin') {
                    return redirect()->route('admin.dashboard');
                }

                if ($guard === 'user') {
                    return redirect()->route('agence.dashboard');
                }

                return redirect('/');
            }
        }

        return $next($request);
    }
}
